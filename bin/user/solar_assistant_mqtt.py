"""
Solar Assistant MQTT Service - v4.5
Uses ACTUAL topic names from your Solar Assistant installation

4.5: solar_data.json also carries an "alltime" block -- energy totals since
     solar data was first recorded (see _update_alltime).
4.6: ...and a "today_peak" block -- today's peak total load and when it
     happened (see _note_load).
4.7: "alltime" also carries the all-time peak total load and when it happened
     (peak_power / peak_time).
"""

import json
import logging
import time
import threading
from datetime import datetime
from typing import Any, Dict

import paho.mqtt.client as mqtt
import weewx
from weewx.engine import StdService

VERSION = "4.7-ALLTIME-PEAK"

log = logging.getLogger(__name__)


# CORRECTED MQTT topic mapping based on actual JSON output
TOPIC_TO_COLUMN = {
    # ==================== SOLAR/PV ====================
    'solar_assistant/inverter_1/pv_power/state': 'pv_power',
    'solar_assistant/inverter_1/pv_voltage_1/state': 'pv_voltage_1',
    'solar_assistant/inverter_1/pv_voltage_2/state': 'pv_voltage_2',
    'solar_assistant/inverter_1/pv_current_1/state': 'pv_current_1',
    'solar_assistant/inverter_1/pv_current_2/state': 'pv_current_2',
    'solar_assistant/inverter_1/pv_power_1/state': 'pv_power_1',
    'solar_assistant/inverter_1/pv_power_2/state': 'pv_power_2',
    # CUMULATIVE - Actual topic names from your system
    'solar_assistant/total/pv_energy/state': 'pv_energy_today',  # ← CORRECTED
    
    # ==================== BATTERY ====================
    'solar_assistant/total/battery_power/state': 'battery_power',
    'solar_assistant/inverter_1/battery_voltage/state': 'battery_voltage',
    'solar_assistant/inverter_1/battery_current/state': 'battery_current',
    'solar_assistant/total/battery_state_of_charge/state': 'battery_soc',
    'solar_assistant/total/battery_temperature/state': 'battery_temp',
    # CUMULATIVE - Actual topic names from your system
    'solar_assistant/total/battery_energy_in/state': 'battery_charge_today',  # ← CORRECTED
    'solar_assistant/total/battery_energy_out/state': 'battery_discharge_today',  # ← CORRECTED
    # Battery settings
    'solar_assistant/inverter_1/battery_absorption_charge_voltage/state': 'battery_absorption_charge_voltage',
    'solar_assistant/inverter_1/battery_float_charge_voltage/state': 'battery_float_charge_voltage',
    'solar_assistant/inverter_1/battery_equalization_charge_voltage/state': 'battery_equalization_charge_voltage',
    'solar_assistant/inverter_1/max_charge_current/state': 'max_charge_current',
    'solar_assistant/inverter_1/max_discharge_current/state': 'max_discharge_current',
    'solar_assistant/inverter_1/max_grid_charge_current/state': 'max_grid_charge_current',
    'solar_assistant/inverter_1/max_generator_charge_current/state': 'max_generator_charge_current',
    
    # ==================== GRID ====================
    'solar_assistant/inverter_1/grid_power/state': 'grid_power',
    'solar_assistant/inverter_1/grid_power_ct/state': 'grid_power_ct',
    'solar_assistant/inverter_1/grid_power_ld/state': 'grid_power_ld',
    'solar_assistant/inverter_1/grid_voltage/state': 'grid_voltage',
    'solar_assistant/inverter_1/grid_frequency/state': 'grid_frequency',
    # CUMULATIVE - Actual topic names from your system
    'solar_assistant/total/grid_energy_in/state': 'grid_import_today',  # ← CORRECTED
    'solar_assistant/total/grid_energy_out/state': 'grid_export_today',  # ← CORRECTED
    # Grid settings
    'solar_assistant/inverter_1/grid_voltage_high/state': 'grid_voltage_high',
    'solar_assistant/inverter_1/grid_voltage_low/state': 'grid_voltage_low',
    'solar_assistant/inverter_1/grid_frequency_high/state': 'grid_frequency_high',
    'solar_assistant/inverter_1/grid_frequency_low/state': 'grid_frequency_low',
    'solar_assistant/inverter_1/grid_trickle_feed/state': 'grid_trickle_feed',
    'solar_assistant/inverter_1/max_sell_power/state': 'max_sell_power',
    'solar_assistant/inverter_1/grid_peak_shaving_power/state': 'grid_peak_shaving_power',
    'solar_assistant/inverter_1/grid_charge/state': 'grid_charge',
    'solar_assistant/inverter_1/grid_peak_shaving/state': 'grid_peak_shaving',
    
    # ==================== LOAD ====================
    'solar_assistant/inverter_1/load_power/state': 'load_power',
    'solar_assistant/inverter_1/load_power_essential/state': 'load_power_essential',
    'solar_assistant/inverter_1/load_power_non-essential/state': 'load_power_non_essential',
    'solar_assistant/inverter_1/load_percentage/state': 'load_percentage',
    # CUMULATIVE - Actual topic name from your system
    'solar_assistant/total/load_energy/state': 'load_energy_today',  # ← ADDED
    
    # ==================== INVERTER ====================
    'solar_assistant/inverter_1/temperature/state': 'inverter_temp',
    'solar_assistant/inverter_1/ac_output_voltage/state': 'ac_output_voltage',
    'solar_assistant/inverter_1/ac_output_frequency/state': 'ac_output_frequency',
    'solar_assistant/inverter_1/device_mode/state': 'device_mode',
    'solar_assistant/inverter_1/work_mode/state': 'work_mode',
    'solar_assistant/inverter_1/energy_pattern/state': 'energy_pattern',
    'solar_assistant/inverter_1/serial_number/state': 'serial_number',
    
    # ==================== GENERATOR ====================
    'solar_assistant/inverter_1/generator_power/state': 'generator_power',
    'solar_assistant/inverter_1/generator_start_voltage/state': 'generator_start_voltage',
    'solar_assistant/inverter_1/generator_stop_voltage/state': 'generator_stop_voltage',
    'solar_assistant/inverter_1/generator_start_capacity/state': 'generator_start_capacity',
    'solar_assistant/inverter_1/generator_stop_capacity/state': 'generator_stop_capacity',
    'solar_assistant/inverter_1/generator_peak_shaving_power/state': 'generator_peak_shaving_power',
    'solar_assistant/inverter_1/generator_charge/state': 'generator_charge',
    'solar_assistant/inverter_1/generator_peak_shaving/state': 'generator_peak_shaving',
    'solar_assistant/inverter_1/force_generator_on/state': 'force_generator_on',
    'solar_assistant/inverter_1/generator_connected_to_grid_input/state': 'generator_connected_to_grid_input',
    
    # ==================== CAPACITY CONTROL ====================
    'solar_assistant/inverter_1/start_battery_discharge_capacity/state': 'start_battery_discharge_capacity',
    'solar_assistant/inverter_1/stop_battery_discharge_capacity/state': 'stop_battery_discharge_capacity',
    'solar_assistant/inverter_1/output_shutdown_capacity/state': 'output_shutdown_capacity',
    
    # ==================== TIMER CONTROL ====================
    'solar_assistant/inverter_1/use_timer/state': 'use_timer',
    
    # ==================== ADDITIONAL SETTINGS ====================
    'solar_assistant/inverter_1/solar_export_when_battery_full/state': 'solar_export_when_battery_full',
    'solar_assistant/inverter_1/auxiliary_load_output_on_grid_always_on/state': 'auxiliary_load_output_on_grid_always_on',
}

# Add timer points
for i in range(1, 7):
    TOPIC_TO_COLUMN[f'solar_assistant/inverter_1/time_point_{i}/state'] = f'time_point_{i}'
    TOPIC_TO_COLUMN[f'solar_assistant/inverter_1/capacity_point_{i}/state'] = f'capacity_point_{i}'
    TOPIC_TO_COLUMN[f'solar_assistant/inverter_1/voltage_point_{i}/state'] = f'voltage_point_{i}'
    TOPIC_TO_COLUMN[f'solar_assistant/inverter_1/grid_charge_point_{i}/state'] = f'grid_charge_point_{i}'
    TOPIC_TO_COLUMN[f'solar_assistant/inverter_1/gen_charge_point_{i}/state'] = f'gen_charge_point_{i}'


class SolarAssistantMQTT(StdService):

    def __init__(self, engine, config_dict):
        super(SolarAssistantMQTT, self).__init__(engine, config_dict)
        self.config_dict = config_dict
        
        log.info("="*80)
        log.info(f"SolarAssistantMQTT: VERSION {VERSION}")
        log.info(f"SolarAssistantMQTT: Using CORRECTED topic names from actual system")
        log.info(f"SolarAssistantMQTT: Mapping {len(TOPIC_TO_COLUMN)} fields")
        log.info("="*80)
        
        sa_config = config_dict.get('SolarAssistant', {})
        
        self.mqtt_broker_host = sa_config.get('mqtt_broker_host', 'localhost')
        self.mqtt_broker_port = int(sa_config.get('mqtt_broker_port', 1883))
        self.mqtt_username = sa_config.get('mqtt_username', '')
        self.mqtt_password = sa_config.get('mqtt_password', '')
        self.mqtt_topic = sa_config.get('mqtt_topic', 'solar_assistant/#')
        self.json_output_path = sa_config.get('json_output_path', '/var/tmp/solar_assistant_data.json')
        self.write_interval = int(sa_config.get('write_interval', 30))
        self.debug_mode = sa_config.get('debug', False)
        
        self.mqtt_data: Dict[str, Any] = {}
        self.data_lock = threading.Lock()
        self.last_write_time = time.time()
        self.message_count = 0
        self.packet_count = 0
        self.connected = False
        self.running = True
        # All-time totals of the days before today (see _update_alltime);
        # today's live counters are added when the JSON is written.
        self.alltime_past = None
        # Today's peak total load: {'day': start-of-day, 'power': W, 'time': epoch}
        self.peak = {'day': None, 'power': None, 'time': None}
        
        self.mqttc = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2, client_id="weewx_solar")
        
        if self.mqtt_username and self.mqtt_password:
            self.mqttc.username_pw_set(self.mqtt_username, self.mqtt_password)
        
        self.mqttc.on_connect = self._on_connect
        self.mqttc.on_message = self._on_message
        self.mqttc.on_disconnect = self._on_disconnect
        
        self.mqtt_thread = threading.Thread(target=self._mqtt_loop, daemon=True)
        self.mqtt_thread.start()
        
        self.writer_thread = threading.Thread(target=self._writer_loop, daemon=True)
        self.writer_thread.start()
        
        self.bind(weewx.NEW_LOOP_PACKET, self.new_loop_packet)
        self.bind(weewx.NEW_ARCHIVE_RECORD, self.new_archive_record)
        self._update_alltime()
        
        log.info("SolarAssistantMQTT: ✅ Initialization complete")

    def _mqtt_loop(self):
        retry_delay = 5
        while self.running:
            try:
                self.mqttc.connect(self.mqtt_broker_host, self.mqtt_broker_port, 60)
                self.mqttc.loop_forever()
            except Exception as e:
                log.error(f"SolarAssistantMQTT: MQTT error: {e}")
                if self.running:
                    time.sleep(retry_delay)
                    retry_delay = min(retry_delay * 2, 300)

    def _writer_loop(self):
        while self.running:
            time.sleep(self.write_interval)
            if self.running:
                self._write_json()

    def _on_connect(self, client, userdata, flags, reason_code, properties):
        if reason_code == 0:
            self.connected = True
            client.subscribe(self.mqtt_topic)
            log.info(f"SolarAssistantMQTT: ✅ MQTT connected")

    def _on_disconnect(self, client, userdata, flags, reason_code, properties):
        self.connected = False

    def _on_message(self, client, userdata, msg):
        try:
            self.message_count += 1
            topic = msg.topic
            payload_str = msg.payload.decode('utf-8')
            
            try:
                value = json.loads(payload_str)
            except:
                try:
                    value = float(payload_str)
                except:
                    value = payload_str
            
            with self.data_lock:
                self.mqtt_data[topic] = {
                    'value': value,
                    'timestamp': datetime.now().isoformat(),
                    'raw': payload_str
                }
                if topic in self.LOAD_TOPICS:
                    self._note_load()
            
            if self.message_count == 1:
                log.info(f"SolarAssistantMQTT: ✅ First MQTT message")
            
        except Exception as e:
            log.error(f"SolarAssistantMQTT: Message error: {e}")

    def new_loop_packet(self, event):
        """Add ALL Solar Assistant data to loop packet."""
        try:
            if self.message_count == 0:
                return
            
            self.packet_count += 1
            fields_added = 0
            cumulative_found = {}
            
            with self.data_lock:
                packet = event.packet
                
                # Add ALL fields
                for topic, column in TOPIC_TO_COLUMN.items():
                    if topic in self.mqtt_data:
                        val = self.mqtt_data[topic]['value']
                        
                        if isinstance(val, bool):
                            val = 1 if val else 0
                        
                        packet[column] = val
                        fields_added += 1
                        
                        # Track cumulative fields
                        if 'energy' in topic:
                            cumulative_found[column] = val
                
                if self.packet_count == 1:
                    log.info(f"SolarAssistantMQTT: ✅ Loop packet augmented ({fields_added} fields)")
                    log.info(f"SolarAssistantMQTT: Real-time: pv={packet.get('pv_power')}W, soc={packet.get('battery_soc')}%")
                    log.info(f"SolarAssistantMQTT: Cumulative totals found: {list(cumulative_found.keys())}")
                    log.info(f"SolarAssistantMQTT: Values: {cumulative_found}")
                elif self.packet_count % 10 == 0 and cumulative_found:
                    log.info(f"SolarAssistantMQTT: Packet #{self.packet_count} cumulative: {cumulative_found}")
            
        except Exception as e:
            log.error(f"SolarAssistantMQTT: Loop packet error: {e}")
            import traceback
            log.error(traceback.format_exc())

    # ==================== ALL-TIME TOTALS ====================
    # Solar Assistant's energy counters reset every day, and DivumWX saves
    # them in its extras database (pv_energy_today, ...). The daily-summary
    # table of each column holds that day's maximum -- the day's final total
    # -- so the all-time figure is the sum of those maxima. Worked out for the
    # days before today on each archive record (main thread, so the engine's
    # database manager can be used safely); today's live counters are added
    # in _write_json so the totals tick up through the day.
    ALLTIME_COLS = {
        'generation': 'pv_energy_today',
        'import': 'grid_import_today',
        'export': 'grid_export_today',
        'usage': 'load_energy_today',
        'batt_in': 'battery_charge_today',
        'batt_out': 'battery_discharge_today',
    }
    ALLTIME_TOPICS = {
        'generation': 'solar_assistant/total/pv_energy/state',
        'import': 'solar_assistant/total/grid_energy_in/state',
        'export': 'solar_assistant/total/grid_energy_out/state',
        'usage': 'solar_assistant/total/load_energy/state',
    }

    # ==================== TODAY'S PEAK LOAD ====================
    # Followed live from every MQTT load message, so short spikes between
    # archive records count. After a restart it's picked up again from the
    # daily summary of load_power_non_essential (see _seed_peak), which only
    # knows the values saved at each archive record.
    # Total load: load_power_non-essential is the total of both circuits
    # (load_power is the house circuit) -- the same readings the cards use.
    LOAD_TOTAL_TOPIC = 'solar_assistant/inverter_1/load_power_non-essential/state'
    LOAD_PART_TOPICS = ('solar_assistant/inverter_1/load_power_essential/state',
                        'solar_assistant/inverter_1/load_power/state')
    LOAD_TOPICS = (LOAD_TOTAL_TOPIC,) + LOAD_PART_TOPICS

    @staticmethod
    def _num(v):
        try:
            return float(v)
        except (TypeError, ValueError):
            return None

    def _peak_candidate(self, power, when):
        """Keep `power` (W) at `when` if it beats today's peak. Called with data_lock held."""
        import weeutil.weeutil
        day = weeutil.weeutil.startOfDay(when)
        if self.peak['day'] != day:
            if self.peak['day'] is not None and day < self.peak['day']:
                return
            self.peak = {'day': day, 'power': None, 'time': None}
        if power is not None and (self.peak['power'] is None or power > self.peak['power']):
            self.peak['power'] = power
            self.peak['time'] = int(when)

    def _note_load(self):
        """Called with data_lock held, after a load message."""
        total = self._num(self.mqtt_data.get(self.LOAD_TOTAL_TOPIC, {}).get('value'))
        if total is None:
            parts = [self._num(self.mqtt_data.get(t, {}).get('value')) for t in self.LOAD_PART_TOPICS]
            if any(p is not None for p in parts):
                total = sum(p or 0 for p in parts)
        self._peak_candidate(total, time.time())

    def _seed_peak(self, dbm, today):
        """Pick today's peak back up from the database (e.g. after a restart)."""
        col = 'load_power_non_essential'
        if col not in getattr(dbm, 'daykeys', ()):
            return
        row = dbm.getSql('SELECT max, maxtime FROM %s_day_%s WHERE dateTime = ?' % (dbm.table_name, col), (today,))
        if row and row[0] is not None and row[1] is not None:
            with self.data_lock:
                self._peak_candidate(float(row[0]), int(row[1]))

    def _peak_block(self):
        """Called with data_lock held."""
        import weeutil.weeutil
        if self.peak['power'] is None or self.peak['day'] != weeutil.weeutil.startOfDay(time.time()):
            return None
        return {'power': round(self.peak['power']), 'time': self.peak['time']}

    def new_archive_record(self, event):
        self._update_alltime()

    def _update_alltime(self):
        try:
            import weeutil.weeutil
            try:
                from user.divumwx import extras_database_exists, EXTRAS_BINDING
                binding = EXTRAS_BINDING if extras_database_exists(self.config_dict) else 'wx_binding'
            except Exception:
                binding = 'wx_binding'
            dbm = self.engine.db_binder.get_manager(binding)
            today = weeutil.weeutil.startOfDay(time.time())
            try:
                self._seed_peak(dbm, today)
            except Exception as e:
                log.debug(f"SolarAssistantMQTT: couldn't read today's peak load from the database: {e}")
            days = {}   # key -> {dayStart: max}
            for key, col in self.ALLTIME_COLS.items():
                if col not in getattr(dbm, 'daykeys', ()):
                    days[key] = {}
                    continue
                rows = dbm.genSql('SELECT dateTime, max FROM %s_day_%s WHERE dateTime < ? AND max IS NOT NULL'
                                  % (dbm.table_name, col), (today,))
                days[key] = {int(r[0]): float(r[1]) for r in rows}

            past = {k: sum(days[k].values()) for k in ('generation', 'import', 'export')}
            # Usage: the recorded figure where there is one; for days before it
            # was recorded, the energy balance (PV + import + battery out
            # - export - battery in), which ignores inverter losses.
            usage, estimated = 0.0, 0
            for d in set(days['generation']) | set(days['import']) | set(days['export']):
                if d in days['usage']:
                    usage += days['usage'][d]
                else:
                    bal = (days['generation'].get(d, 0) + days['import'].get(d, 0) + days['batt_out'].get(d, 0)
                           - days['export'].get(d, 0) - days['batt_in'].get(d, 0))
                    usage += max(bal, 0.0)
                    estimated += 1
            past['usage'] = usage
            first = [min(v) for k, v in days.items() if v and k in ('generation', 'import', 'export', 'usage')]
            # Highest total load on any day before today (from the saved
            # archive records, so a spike between two records isn't seen).
            past_peak = None
            if 'load_power_non_essential' in getattr(dbm, 'daykeys', ()):
                row = dbm.getSql('SELECT max, maxtime FROM %s_day_load_power_non_essential '
                                 'WHERE dateTime < ? AND max IS NOT NULL AND maxtime IS NOT NULL '
                                 'ORDER BY max DESC LIMIT 1' % dbm.table_name, (today,))
                if row:
                    past_peak = (float(row[0]), int(row[1]))
            alltime = {
                'since': min(first) if first else today,
                'usage_estimated_days': estimated,
                'past': past,
                'past_peak': past_peak,
            }
            with self.data_lock:
                self.alltime_past = alltime
        except Exception as e:
            log.warning(f"SolarAssistantMQTT: all-time totals unavailable: {e}")

    def _alltime_block(self):
        """All-time totals in kWh (past days + today's live counters). Called with data_lock held."""
        if not self.alltime_past:
            return None
        out = {'since': self.alltime_past['since'],
               'usage_estimated_days': self.alltime_past['usage_estimated_days']}
        for key, topic in self.ALLTIME_TOPICS.items():
            today = self.mqtt_data.get(topic, {}).get('value')
            try:
                today = float(today)
            except (TypeError, ValueError):
                today = 0.0
            out[key] = round(self.alltime_past['past'].get(key, 0.0) + today, 2)
        # All-time peak: the best earlier day, or today's live peak if higher.
        best = self.alltime_past.get('past_peak')
        tp = self._peak_block()
        if tp and (best is None or tp['power'] > best[0]):
            best = (tp['power'], tp['time'])
        out['peak_power'] = round(best[0]) if best else None
        out['peak_time'] = best[1] if best else None
        return out

    def _write_json(self):
        try:
            with self.data_lock:
                data_to_write = {
                    'last_updated': datetime.now().isoformat(),
                    'message_count': self.message_count,
                    'packet_count': self.packet_count,
                    'connected': self.connected,
                    'unique_topics': len(self.mqtt_data),
                    'alltime': self._alltime_block(),
                    'today_peak': self._peak_block(),
                    'data': dict(self.mqtt_data)
                }
            
            with open(self.json_output_path, 'w') as f:
                json.dump(data_to_write, f, indent=2)
            
        except Exception as e:
            log.error(f"SolarAssistantMQTT: JSON error: {e}")

    def shutDown(self):
        log.info(f"SolarAssistantMQTT: Shutting down...")
        self.running = False
        self._write_json()
        self.mqttc.disconnect()
        
        if self.mqtt_thread.is_alive():
            self.mqtt_thread.join(timeout=5)
        if self.writer_thread.is_alive():
            self.writer_thread.join(timeout=5)
        
        log.info("SolarAssistantMQTT: ✅ Shutdown complete")
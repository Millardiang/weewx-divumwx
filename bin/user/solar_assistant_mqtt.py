"""
Solar Assistant MQTT Service - v4.4
Uses ACTUAL topic names from your Solar Assistant installation
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

VERSION = "4.4-CORRECTED-TOPICS"

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

    def _write_json(self):
        try:
            with self.data_lock:
                data_to_write = {
                    'last_updated': datetime.now().isoformat(),
                    'message_count': self.message_count,
                    'packet_count': self.packet_count,
                    'connected': self.connected,
                    'unique_topics': len(self.mqtt_data),
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
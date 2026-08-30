/* DivumWX ECharts (v6 responsive JS) */
(function(){
  const BASE = new URL('echarts/', location.href).pathname.replace(/\/+$/,'') + '/';
  const bust = (u)=> u + (u.includes('?')?'&':'?') + 't=' + Date.now();
  const $ = (sel, el=document)=> el.querySelector(sel);
  const $$ = (sel, el=document)=> Array.from(el.querySelectorAll(sel));
  const fmt = (ms)=> new Date(ms).toLocaleString('en-GB',{hour12:false});

  // error overlay
  const errBox = $('#errOverlay');
  const showOverlay = (txt)=>{ errBox.hidden=false; errBox.textContent = txt; };

  async function getJSON(name){
    const url = bust(BASE + name);
    const r = await fetch(url, {cache:'no-cache'});
    if(!r.ok) throw new Error(`${r.status} ${r.statusText} for ${url}`);
    return r.json();
  }

  const instances = new WeakMap();
  function setError(card, msg){ const e=$('.err',card); if(e){ e.style.display='block'; e.textContent='⚠️ '+msg; } }
  function clearError(card){ const e=$('.err',card); if(e){ e.style.display='none'; } }

  function baseOption(yName='', type='line') {
    return {
      tooltip: { trigger:'axis' },
      legend: { top: 6, right: 8, textStyle: { color: 'inherit', fontSize: 11 } },
      grid:   { left: 52, right: 14, top: 34, bottom: 30 },
      xAxis: {
        type:'time',
        axisLabel:{ color:'var(--muted)', fontSize:11 },
        axisLine:{ lineStyle:{ color:'var(--border)' } },
        splitLine:{ show:true, lineStyle:{ color:'rgba(255,255,255,.06)' } }
      },
      yAxis: {
        type:'value', name: yName, nameGap: 18,
        nameTextStyle:{ color:'var(--muted)', fontSize:11 },
        axisLabel:{ color:'var(--muted)', fontSize:11 },
        axisLine:{ lineStyle:{ color:'var(--border)' } },
        splitLine:{ show:true, lineStyle:{ color:'rgba(255,255,255,.06)' } },
        scale:true
      },
      series: []
    };
  }

  function makeCard({id, title, file, type, series}){
    const card = document.createElement('div');
    card.className = 'card';
    card.dataset.id = id;
    card.dataset.file = file;
    card.dataset.type = type || 'line';
    card.dataset.series = (series||[]).join(',');
    card.dataset.title = title || id;
    card.innerHTML = `<div class="title">${title||id}</div><div class="chart"></div><div class="err"></div>`;
    return card;
  }

  async function renderCard(card){
    clearError(card);
    const file  = card.dataset.file;
    const type  = (card.dataset.type || 'line').toLowerCase();
    const ser   = (card.dataset.series || '').split(',').map(s=>s.trim()).filter(Boolean);
    const chartEl = $('.chart', card);
    let inst = instances.get(card);
    if (!inst) { inst = echarts.init(chartEl); instances.set(card, inst); }

    try{
      const data = await getJSON(file);

      // meta → show tz and last update if present
      if (data?.meta?.station_tz) $('#tzVal').textContent = data.meta.station_tz;
      const firstSeries = (data.series||[])[0];
      const last = firstSeries?.data?.at?.(-1)?.[0];
      if (last) $('#updVal').textContent = fmt(last);

      function pickSeries(payload){
        if (!ser.length) return payload.series||[];
        const map = Object.fromEntries((payload.series||[]).map(s=>[s.name,s]));
        return ser.map(n=>map[n]).filter(Boolean);
      }

      let option = baseOption('', type);

      if (file === '30d_rain_daily.json') {
        const s = (data.series||[])[0];
        option = baseOption(s?.units || '', 'bar');
        option.series = [{ type:'bar', name:'Daily rain', data:s?.data || [], barMaxWidth:12 }];
      } else if (file === '365d_temp_daily.json') {
        const minS = (data.series||[]).find(s=>s.name==='outTempMin');
        const maxS = (data.series||[]).find(s=>s.name==='outTempMax');
        option = baseOption(maxS?.units || minS?.units || '', 'line');
        option.series = [];
        if (minS) option.series.push({ type:'line', name:'Min', data:minS.data, showSymbol:false, smooth:true });
        if (maxS) option.series.push({ type:'line', name:'Max', data:maxS.data, showSymbol:false, smooth:true });
      } else {
        const picked = pickSeries(data);
        if (!picked.length) throw new Error('No matching series in '+file);
        option = baseOption(picked[0]?.units || '', type);
        option.series = picked.map(s => ({
          type: (type==='bar' ? 'bar' : 'line'),
          name: s.name, data: s.data, showSymbol:false, smooth:true,
          ...(type==='bar' ? {barMaxWidth:12} : {})
        }));
      }

      inst.setOption(option, true, true);
    }catch(e){
      setError(card, e.message);
      console.error(e);
      showOverlay(e.message);
    }
  }

  function renderAll(){ $$('#grid .card').forEach(renderCard); }

  // Modal
  const modal = $('#modal');
  const modalTitle = $('#modalTitle');
  const modalChartEl = $('#modalChart');
  let modalInst = null;
  let onWinResize = null;

  // calculate responsive paddings for modal charts
  function responsiveEnhance(opt){
    const w = window.innerWidth || 1024;
    const isSmall = w < 680;
    const grid = Object.assign({}, opt.grid || {});
    grid.left   = isSmall ? 48 : 70;
    grid.right  = isSmall ? 16 : 24;
    grid.top    = isSmall ? 40 : 48;
    grid.bottom = isSmall ? 84 : 96;

    // Lightly reduce axis label sizes on phones
    const xAxis = Array.isArray(opt.xAxis) ? opt.xAxis[0] : (opt.xAxis || {});
    const yAxis = Array.isArray(opt.yAxis) ? opt.yAxis[0] : (opt.yAxis || {});
    const xLbl = Object.assign({}, xAxis.axisLabel || {});
    const yLbl = Object.assign({}, yAxis.axisLabel || {});
    xLbl.fontSize = isSmall ? 10 : 11;
    yLbl.fontSize = isSmall ? 10 : 11;

    return Object.assign({}, opt, {
      grid,
      xAxis: Object.assign({}, xAxis, { axisLabel: xLbl }),
      yAxis: Object.assign({}, yAxis, { axisLabel: yLbl }),
      dataZoom: [
        {type:'inside', start:50, end:100},
        {type:'slider', start:50, end:100, bottom: isSmall ? 10 : 12, height: isSmall ? 18 : 22}
      ]
    });
  }

  function openModalFromCard(card){
    const inst = instances.get(card);
    if (!inst) return;
    const opt = inst.getOption();
    modalTitle.textContent = card.dataset.title || card.dataset.id || 'Chart';
    if (modalInst) { modalInst.dispose(); modalInst=null; }
    modalInst = echarts.init(modalChartEl);

    const enhanced = responsiveEnhance(opt);
    modalInst.setOption(enhanced, true, true);
    modal.classList.add('open');

    // responsive on resize while open
    onWinResize = ()=>{
      if (!modalInst) return;
      modalInst.resize();
      const o = modalInst.getOption();
      modalInst.setOption(responsiveEnhance(o), false, false);
    };
    window.addEventListener('resize', onWinResize);
    // also support Esc to close
    const onKey = (e)=>{ if(e.key==='Escape') closeModal(); };
    window.addEventListener('keydown', onKey, { once:true });

    setTimeout(()=>modalInst.resize(),0);
  }

  function closeModal(){
    modal.classList.remove('open');
    modalInst?.dispose(); modalInst=null;
    if (onWinResize){ window.removeEventListener('resize', onWinResize); onWinResize=null; }
  }

  $('#modalClose').addEventListener('click', closeModal);
  $('#modal').addEventListener('click', (e)=>{ if(e.target===modal){ closeModal(); } });

  $('#grid').addEventListener('click', (e)=>{
    const card = e.target.closest('.card');
    if (card) openModalFromCard(card);
  });

  // Build grid: robust to missing files
  async function buildGrid(){
    const grid = $('#grid');
    grid.innerHTML = '';
    let charts = null;

    // Try charts.manifest.json
    try{
      const manifest = await getJSON('charts.manifest.json');
      charts = manifest?.charts;
      // also try fields_24h.json for picker
      try{
        const fields = (await getJSON('fields_24h.json')).fields || [];
        const sel = $('#fieldSelect'); sel.innerHTML='';
        fields.forEach(f=>{ const o=document.createElement('option'); o.value=f; o.textContent=f; sel.appendChild(o); });
      }catch{ /* ignore */ }
    }catch{
      // Fallback: infer from 24h_core.json
      try{
        const core = await getJSON('24h_core.json');
        const names = (core.series||[]).map(s=>s.name);
        const sel = $('#fieldSelect'); sel.innerHTML='';
        names.forEach(f=>{ const o=document.createElement('option'); o.value=f; o.textContent=f; sel.appendChild(o); });
      }catch{ /* ignore */ }
    }

    if (!charts || !charts.length){
      charts = [
        {id:'temp24', file:'24h_core.json', title:'Temperature • 24h', type:'line', series:['outTemp']},
        {id:'rain30', file:'30d_rain_daily.json', title:'Rain • 30 days', type:'bar', series:[]},
        {id:'t365',   file:'365d_temp_daily.json', title:'Temperature • 365 days (min/max)', type:'line', series:['outTempMin','outTempMax']}
      ];
    }

    charts.forEach(ch=>{
      const card = makeCard(ch);
      $('#grid').appendChild(card);
    });
    renderAll();
  }

  // Add field card from picker
  $('#addField').addEventListener('click', ()=>{
    const field = $('#fieldSelect').value;
    if (!field) return;
    const card = makeCard({id:`f_${field}`, title:`${field} • 24h`, file:`24h_${field}.json`, type:'line', series:[field]});
    $('#grid').appendChild(card);
    renderCard(card);
  });
  $('#refresh').addEventListener('click', buildGrid);

  // Kick off
  buildGrid();
})();
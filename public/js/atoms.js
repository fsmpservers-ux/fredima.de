'use strict';

/* ══════════════════════════════════════════════════
   ATOM-SIDEBAR – DOM-Referenzen
══════════════════════════════════════════════════ */
const sidebar = document.getElementById('atom-sidebar');
const eraEl   = document.getElementById('atom-era');
const labelEl = document.getElementById('atom-label');

/* ══════════════════════════════════════════════════
   REVEAL.JS INITIALISIERUNG
   embedded: false  →  Presenter-Mode (S-Taste) funktioniert!
   Layout per CSS Flexbox (#wrapper) statt embedded-Mode
══════════════════════════════════════════════════ */
Reveal.initialize({
  plugins: [ RevealNotes ],

  /* ─ Layout ─ */
  // embedded: true  ← ENTFERNT: blockiert den Presenter-Mode
  width:    960,
  height:   700,
  margin:   0.04,
  minScale: 0.3,
  maxScale: 1.5,

  /* ─ Navigation ─ */
  hash:         true,
  history:      false,
  controls:     true,
  controlsLayout: 'bottom-right',
  progress:     true,
  slideNumber:  'c/t',

  /* ─ Tastatur ─ */
  keyboard:     true,

  /* ─ Übergänge ─ */
  transition:           'zoom',
  transitionSpeed:      'slow',
  backgroundTransition: 'fade',

  /* ─ Übersicht ─ */
  overview: true,

  /* ─ Touch / Swipe ─ */
  touch: true,
});

/* ══════════════════════════════════════════════════
   SIDEBAR  –  reagiert auf Folien-Wechsel
══════════════════════════════════════════════════ */
function applySidebar(slide) {
  if (!slide) return;
  const show = slide.dataset.sidebar !== 'hide';
  const atom = slide.dataset.atom  || 'none';
  const era  = slide.dataset.era   || '';

  sidebar.classList.toggle('hidden', !show);
  eraEl.textContent   = era;
  labelEl.textContent = atomMeta[atom]?.label || '';
  transitionAtom(atom);

  setTimeout(() => Reveal.layout(), 750);
}

Reveal.on('ready',        e => applySidebar(e.currentSlide));
Reveal.on('slidechanged', e => applySidebar(e.currentSlide));

/* ══════════════════════════════════════════════════
   CANVAS  –  Atom-Renderer
══════════════════════════════════════════════════ */
const canvas = document.getElementById('atomCanvas');
const ctx    = canvas.getContext('2d');
const W = canvas.width, H = canvas.height, CX = W / 2, CY = H / 2;

let animT       = 0;
let fromType    = 'none';
let toType      = 'none';
let transitioning = false;
let transitionT = 0;
const TDUR      = 55;

const atomMeta = {
  none:       { label: '' },
  demokrit:   { label: 'Demokrits Atomos · ~450 v.Chr.' },
  dalton:     { label: 'Dalton-Modell · 1803' },
  thomson:    { label: 'Thomson-Modell · 1897' },
  rutherford: { label: 'Rutherford-Modell · 1911' },
  neutron:    { label: 'Kern-Modell + Nucleonen · 1932' },
  bohr:       { label: 'Bohrsches Schalenmodell · 1913' },
  quantum:    { label: 'Quantenmechanisches Modell · heute' },
};

function transitionAtom(nType) {
  if (nType === toType) return;
  fromType = toType; toType = nType;
  transitioning = true; transitionT = 0;
}

function grd(x,y,r0,r1,stops) {
  const g = ctx.createRadialGradient(x,y,r0,x,y,r1);
  stops.forEach(([t,c]) => g.addColorStop(t,c));
  return g;
}
function circ(x,y,r) { ctx.beginPath(); ctx.arc(x,y,r,0,Math.PI*2); }

function drawNone(a) {
  ctx.globalAlpha = a * .18;
  ctx.strokeStyle = '#3ee8bb'; ctx.lineWidth = 1;
  ctx.setLineDash([2,9]); circ(CX,CY,80); ctx.stroke();
  ctx.setLineDash([]); ctx.globalAlpha = 1;
}

function drawDemokrit(a,t) {
  const bob = Math.sin(t*.016)*5;
  ctx.globalAlpha = a;
  ctx.fillStyle = grd(CX,CY+bob,30,140,[[0,'rgba(100,140,255,.12)'],[1,'rgba(100,140,255,0)']]);
  circ(CX,CY+bob,140); ctx.fill();
  const r = 74 + Math.sin(t*.018)*5;
  ctx.fillStyle = grd(CX-22,CY-22+bob,8,r,[[0,'rgba(225,235,255,.95)'],[.35,'rgba(130,165,255,.75)'],[.75,'rgba(55,85,200,.5)'],[1,'rgba(18,38,120,.08)']]);
  circ(CX,CY+bob,r); ctx.fill();
  ctx.strokeStyle = `rgba(190,215,255,${.45*a})`; ctx.lineWidth = 1.5; ctx.stroke();
  ctx.fillStyle = grd(CX-22,CY-26+bob,0,28,[[0,'rgba(255,255,255,.6)'],[1,'rgba(255,255,255,0)']]);
  circ(CX,CY+bob,r); ctx.fill();
  ctx.globalAlpha = a*.8; ctx.fillStyle = '#e8eaf6';
  ctx.font = "italic bold 17px 'Playfair Display',serif";
  ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
  ctx.fillText('ἄτομος',CX,CY+bob);
  for(let i=0;i<8;i++){
    const ang=(i/8)*Math.PI*2+t*.007, pr=100+Math.sin(t*.02+i*.8)*10;
    ctx.globalAlpha = a*.28; ctx.fillStyle = '#8090ff';
    circ(CX+Math.cos(ang)*pr, CY+Math.sin(ang)*pr*.5, 2); ctx.fill();
  }
  ctx.globalAlpha = 1; ctx.textBaseline = 'alphabetic';
}

function drawDalton(a,t) {
  const bob = Math.sin(t*.014)*3;
  ctx.globalAlpha = a;
  ctx.fillStyle = grd(CX,CY+bob,40,130,[[0,'rgba(240,192,48,.07)'],[1,'rgba(240,192,48,0)']]);
  circ(CX,CY+bob,130); ctx.fill();
  ctx.fillStyle = grd(CX-22,CY-22+bob,6,72,[[0,'rgba(255,245,180,1)'],[.28,'rgba(248,198,52,1)'],[.65,'rgba(175,118,10,1)'],[1,'rgba(75,45,0,1)']]);
  circ(CX,CY+bob,72); ctx.fill();
  ctx.fillStyle = grd(CX-20,CY-22+bob,0,30,[[0,'rgba(255,255,255,.65)'],[.5,'rgba(255,255,255,.1)'],[1,'rgba(255,255,255,0)']]);
  circ(CX,CY+bob,72); ctx.fill();
  ctx.strokeStyle = `rgba(255,205,80,${.28*a})`; ctx.lineWidth = 2;
  circ(CX,CY+bob,72); ctx.stroke();
  ctx.globalAlpha = 1;
}

function drawThomson(a,t) {
  const pulse = Math.sin(t*.019)*6;
  ctx.globalAlpha = a;
  ctx.fillStyle = grd(CX,CY,8,90+pulse,[[0,'rgba(255,150,55,.88)'],[.5,'rgba(215,85,25,.52)'],[.82,'rgba(155,35,8,.16)'],[1,'rgba(155,35,8,0)']]);
  circ(CX,CY,90+pulse); ctx.fill();
  [{r:26,a0:.5,spd:.006},{r:50,a0:2.1,spd:-.004},{r:36,a0:3.8,spd:.005},
   {r:63,a0:1.2,spd:-.007},{r:20,a0:4.5,spd:.009},{r:68,a0:5.8,spd:-.003},{r:42,a0:0,spd:.008}]
  .forEach(ep => {
    const ang=ep.a0+t*ep.spd, ex=CX+Math.cos(ang)*ep.r, ey=CY+Math.sin(ang)*ep.r;
    ctx.globalAlpha = a*.32;
    ctx.fillStyle = grd(ex,ey,0,14,[[0,'rgba(62,232,187,.8)'],[1,'rgba(62,232,187,0)']]);
    circ(ex,ey,14); ctx.fill();
    ctx.globalAlpha = a; ctx.fillStyle = '#3ee8bb'; circ(ex,ey,5.5); ctx.fill();
    ctx.globalAlpha = a*.7; ctx.fillStyle = '#070b13'; ctx.fillRect(ex-3,ey-.8,6,1.6);
  });
  ctx.globalAlpha = 1;
}

function drawRutherford(a,t) {
  ctx.globalAlpha = a;
  [{rx:110,ry:33,tilt:0,spd:.022},{rx:96,ry:36,tilt:1.15,spd:-.018},{rx:118,ry:28,tilt:-.7,spd:.016}]
  .forEach(orb => {
    ctx.save(); ctx.translate(CX,CY); ctx.rotate(orb.tilt);
    ctx.strokeStyle = `rgba(62,232,187,${.18*a})`; ctx.lineWidth = 1; ctx.setLineDash([3,6]);
    ctx.beginPath(); ctx.ellipse(0,0,orb.rx,orb.ry,0,0,Math.PI*2); ctx.stroke(); ctx.setLineDash([]);
    const ea = t*orb.spd;
    for(let tr=1;tr<=14;tr++){
      const ta=ea-tr*.09; ctx.globalAlpha = a*(.055*(15-tr)/15); ctx.fillStyle = '#3ee8bb';
      circ(Math.cos(ta)*orb.rx, Math.sin(ta)*orb.ry, 3.5); ctx.fill();
    }
    const ex=Math.cos(ea)*orb.rx, ey=Math.sin(ea)*orb.ry;
    ctx.globalAlpha = a*.38;
    ctx.fillStyle = grd(ex,ey,0,16,[[0,'rgba(62,232,187,.9)'],[1,'rgba(62,232,187,0)']]);
    circ(ex,ey,16); ctx.fill();
    ctx.globalAlpha = a; ctx.fillStyle = '#3ee8bb'; circ(ex,ey,5); ctx.fill();
    ctx.restore();
  });
  ctx.globalAlpha = a*.28;
  ctx.fillStyle = grd(CX,CY,0,28,[[0,'rgba(240,192,48,.9)'],[1,'rgba(240,192,48,0)']]);
  circ(CX,CY,28); ctx.fill();
  ctx.globalAlpha = a;
  ctx.fillStyle = grd(CX-4,CY-4,1,13,[[0,'#fff'],[.3,'#f0c030'],[1,'#c08010']]);
  circ(CX,CY,13); ctx.fill();
  ctx.globalAlpha = 1;
}

function drawNeutron(a,t) {
  ctx.globalAlpha = a;
  [{rx:106,ry:31,tilt:.2,spd:.022},{rx:90,ry:35,tilt:1.3,spd:-.018},{rx:112,ry:27,tilt:-.5,spd:.014}]
  .forEach(orb => {
    ctx.save(); ctx.translate(CX,CY); ctx.rotate(orb.tilt);
    ctx.strokeStyle = `rgba(62,232,187,${.17*a})`; ctx.lineWidth = 1; ctx.setLineDash([3,6]);
    ctx.beginPath(); ctx.ellipse(0,0,orb.rx,orb.ry,0,0,Math.PI*2); ctx.stroke(); ctx.setLineDash([]);
    const ea = t*orb.spd;
    for(let tr=1;tr<=12;tr++){
      const ta=ea-tr*.09; ctx.globalAlpha = a*(.055*(13-tr)/13); ctx.fillStyle = '#3ee8bb';
      circ(Math.cos(ta)*orb.rx, Math.sin(ta)*orb.ry, 3.2); ctx.fill();
    }
    ctx.globalAlpha = a; ctx.fillStyle = '#3ee8bb';
    circ(Math.cos(ea)*orb.rx, Math.sin(ea)*orb.ry, 5); ctx.fill();
    ctx.restore();
  });
  ctx.globalAlpha = a*.22;
  ctx.fillStyle = grd(CX,CY,0,30,[[0,'rgba(240,90,90,.9)'],[1,'rgba(240,90,90,0)']]);
  circ(CX,CY,30); ctx.fill();
  ctx.globalAlpha = a;
  [{x:-6,y:-6,p:true},{x:7,y:-4,p:false},{x:0,y:7,p:true},{x:-7,y:5,p:false},
   {x:8,y:6,p:true},{x:0,y:-3,p:false},{x:-3,y:1,p:true}]
  .forEach(n => {
    const col = n.p ? '#f05a5a' : '#7878f0';
    ctx.fillStyle = grd(CX+n.x-2,CY+n.y-2,0,6,[[0,'#fff'],[.3,col],[1,col.replace(')',',0.6)').replace('rgb','rgba')]]);
    circ(CX+n.x, CY+n.y, 5.5); ctx.fill();
  });
  ctx.globalAlpha = 1;
}

function drawBohr(a,t) {
  ctx.globalAlpha = a;
  [{r:48,eN:2,col:'#3ee8bb',spd:.045},{r:82,eN:4,col:'#d45af0',spd:-.028},{r:118,eN:3,col:'#3ee8bb',spd:.018}]
  .forEach(sh => {
    ctx.globalAlpha = a*.055; ctx.strokeStyle = sh.col; ctx.lineWidth = 7;
    circ(CX,CY,sh.r); ctx.stroke();
    ctx.globalAlpha = a*.2; ctx.lineWidth = 1;
    circ(CX,CY,sh.r); ctx.stroke();
    for(let e=0;e<sh.eN;e++){
      const ang=t*sh.spd+(e/sh.eN)*Math.PI*2, ex=CX+Math.cos(ang)*sh.r, ey=CY+Math.sin(ang)*sh.r;
      for(let tr=1;tr<=20;tr++){
        const ta=ang-tr*.09; ctx.globalAlpha = a*(.038*(21-tr)/21); ctx.fillStyle = sh.col;
        circ(CX+Math.cos(ta)*sh.r, CY+Math.sin(ta)*sh.r, 3.5); ctx.fill();
      }
      ctx.globalAlpha = a*.45;
      ctx.fillStyle = grd(ex,ey,0,17,[[0,sh.col],[1,sh.col+'00']]);
      circ(ex,ey,17); ctx.fill();
      ctx.globalAlpha = a; ctx.fillStyle = sh.col; circ(ex,ey,5.5); ctx.fill();
    }
  });
  ctx.globalAlpha = a*.32;
  ctx.fillStyle = grd(CX,CY,0,34,[[0,'rgba(240,192,48,.9)'],[1,'rgba(240,192,48,0)']]);
  circ(CX,CY,34); ctx.fill();
  ctx.globalAlpha = a;
  ctx.fillStyle = grd(CX-4,CY-4,1,15,[[0,'#fff'],[.3,'#f0c030'],[1,'#b07010']]);
  circ(CX,CY,15); ctx.fill();
  ctx.globalAlpha = 1;
}

function drawQuantum(a,t) {
  const pA=t*.011, dA=t*.0065;
  ctx.globalAlpha = a;
  ctx.fillStyle = grd(CX,CY,0,58,[[0,`rgba(62,232,187,${.52*a})`],[.4,`rgba(62,232,187,${.16*a})`],[.75,`rgba(62,232,187,${.04*a})`],[1,'rgba(62,232,187,0)']]);
  circ(CX,CY,58); ctx.fill();
  [0,Math.PI].forEach((off,li) => {
    const ang=pA+off, pLen=92+Math.sin(t*.024)*8, lcx=CX+Math.cos(ang)*(pLen*.48), lcy=CY+Math.sin(ang)*(pLen*.48), lr=44+Math.sin(t*.02+li*1.1)*5;
    ctx.save(); ctx.translate(lcx,lcy);
    ctx.fillStyle = grd(0,0,0,lr,[[0,`rgba(212,90,240,${.48*a})`],[.45,`rgba(212,90,240,${.18*a})`],[1,'rgba(212,90,240,0)']]);
    circ(0,0,lr); ctx.fill(); ctx.restore();
  });
  for(let lobe=0;lobe<4;lobe++){
    const la=dA+(lobe/4)*Math.PI*2, lcx=CX+Math.cos(la)*(136*.52), lcy=CY+Math.sin(la)*(136*.38), lr=33+Math.sin(t*.016+lobe*.8)*6;
    ctx.save(); ctx.translate(lcx,lcy);
    ctx.fillStyle = grd(0,0,0,lr,[[0,`rgba(240,192,48,${.38*a})`],[.5,`rgba(240,192,48,${.1*a})`],[1,'rgba(240,192,48,0)']]);
    circ(0,0,lr); ctx.fill(); ctx.restore();
  }
  ctx.globalAlpha = a*.6;
  for(let i=0;i<90;i++){
    const seed=i*137.508, theta=seed%(Math.PI*2);
    if(i<27){
      const r=5+((i*7919)%48); ctx.fillStyle = `rgba(62,232,187,${.55*a})`;
      circ(CX+Math.cos(theta+t*.003)*r, CY+Math.sin(theta+t*.003)*r*.95, 1.3); ctx.fill();
    } else if(i<56){
      const side=i%2===0?1:-1, pr=50+((i*3571)%45), pa=pA+((i*1.618)%1.4)-.7;
      ctx.fillStyle = `rgba(212,90,240,${.5*a})`;
      circ(CX+Math.cos(pa)*pr*side, CY+Math.sin(pa)*pr*side*.85, 1.3); ctx.fill();
    } else {
      const lb=i%4, la2=dA+(lb/4)*Math.PI*2+((i*.05)%.9)-.45, lr2=88+((i*2311)%55);
      ctx.fillStyle = `rgba(240,192,48,${.48*a})`;
      circ(CX+Math.cos(la2)*lr2*.88, CY+Math.sin(la2)*lr2*.52, 1.3); ctx.fill();
    }
  }
  const pulse = 1+Math.sin(t*.038)*.18;
  ctx.globalAlpha = a*.38*(.65+Math.sin(t*.038)*.35);
  ctx.fillStyle = grd(CX,CY,0,24*pulse,[[0,'rgba(255,255,255,.9)'],[.3,'rgba(240,192,48,.7)'],[1,'rgba(240,192,48,0)']]);
  circ(CX,CY,24*pulse); ctx.fill();
  ctx.globalAlpha = a;
  ctx.fillStyle = grd(CX-2,CY-2,0,7,[[0,'#fff'],[.5,'#f0c030'],[1,'#b07010']]);
  circ(CX,CY,7); ctx.fill();
  ctx.globalAlpha = 1;
}

const drawFns = {
  none: drawNone, demokrit: drawDemokrit, dalton: drawDalton,
  thomson: drawThomson, rutherford: drawRutherford, neutron: drawNeutron,
  bohr: drawBohr, quantum: drawQuantum,
};

function render() {
  ctx.clearRect(0,0,W,H);
  animT++;
  ctx.strokeStyle = 'rgba(62,232,187,.03)'; ctx.lineWidth = 1;
  for(let x=0;x<W;x+=28){ ctx.beginPath(); ctx.moveTo(x,0); ctx.lineTo(x,H); ctx.stroke(); }
  for(let y=0;y<H;y+=28){ ctx.beginPath(); ctx.moveTo(0,y); ctx.lineTo(W,y); ctx.stroke(); }

  if(transitioning) {
    transitionT++;
    const p = Math.min(transitionT/TDUR, 1);
    const e = p<.5 ? 4*p*p*p : 1-Math.pow(-2*p+2,3)/2;
    if(drawFns[fromType]) drawFns[fromType](1-e, animT);
    if(drawFns[toType])   drawFns[toType](e, animT);
    if(p >= 1) transitioning = false;
  } else {
    if(drawFns[toType]) drawFns[toType](1, animT);
  }

  requestAnimationFrame(render);
}

render();

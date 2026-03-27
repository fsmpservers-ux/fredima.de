// ══════════════════════════════════════════════
// SLIDE CONTROLLER
// ══════════════════════════════════════════════
const slides = document.querySelectorAll('.slide');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
const counter = document.getElementById('slide-counter');
const progressBar = document.getElementById('progress-bar');
let current = 0;

function goTo(n) {
  if (n < 0 || n >= slides.length) return;
  slides[current].classList.remove('active');
  slides[current].classList.add('out-left');
  setTimeout(() => slides[current - (n > current ? 1 : -1) + (n > current ? 1 : -1)].classList.remove('out-left'), 600);

  // more robust cleanup
  slides.forEach(s => s.classList.remove('active', 'out-left'));

  current = n;
  slides[current].classList.add('active');

  counter.textContent = `${current + 1} / ${slides.length}`;
  progressBar.style.width = `${((current) / (slides.length - 1)) * 100}%`;
  prevBtn.disabled = current === 0;
  nextBtn.disabled = current === slides.length - 1;

  // update atom
  const atomType = slides[current].dataset.atom || 'none';
  const era = slides[current].dataset.era || '';
  document.getElementById('atom-era').textContent = era;
  transitionAtom(atomType);
}

prevBtn.addEventListener('click', () => goTo(current - 1));
nextBtn.addEventListener('click', () => goTo(current + 1));
document.addEventListener('keydown', e => {
  if (e.key === 'ArrowRight' || e.key === ' ') goTo(current + 1);
  if (e.key === 'ArrowLeft') goTo(current - 1);
});

// ══════════════════════════════════════════════
// 3D ATOM CANVAS
// ══════════════════════════════════════════════
const canvas = document.getElementById('atomCanvas');
const ctx = canvas.getContext('2d');
const W = canvas.width, H = canvas.height;
const CX = W / 2, CY = H / 2;

let atomState = {
  type: 'none',
  targetType: 'none',
  progress: 1.0,
  t: 0,
  transitionFrom: null,
};

const COLORS = {
  nucleus: '#f5c842',
  proton: '#ff5c5c',
  neutron: '#8080ff',
  electron: '#4af0c8',
  electron2: '#e05aff',
  cloud: 'rgba(74,240,200,',
  orbit: 'rgba(74,240,200,',
};

// atom definitions
const atomConfigs = {
  none: { label: '— Kein Modell —' },
  demokrit: { label: 'Demokrits Atomos (~450 v.Chr.)' },
  dalton: { label: 'Dalton-Modell (1803)' },
  thomson: { label: 'Thomson-Modell (1897)' },
  rutherford: { label: 'Rutherford-Modell (1911)' },
  neutron: { label: 'Kern-Modell mit Neutronen (1932)' },
  bohr: { label: 'Bohrsches Schalenmodell (1913)' },
  quantum: { label: 'Quantenmechanisches Modell (heute)' },
};

let animT = 0;
let transitionAlpha = 1;
let fromType = 'none';
let toType = 'none';
let transitioning = false;
let transitionT = 0;
const TRANSITION_DURATION = 60; // frames

function transitionAtom(newType) {
  if (newType === toType) return;
    createTransitionParticles(toType, newType); // NEU
    fromType = toType;
    toType = newType;
    transitioning = true;
    transitionT = 0;
    document.getElementById('atom-label').textContent = atomConfigs[newType]?.label || '';
}


// ─── DRAW FUNCTIONS ───────────────────────────

function drawNone(alpha) {
  ctx.globalAlpha = alpha * 0.3;
  ctx.strokeStyle = COLORS.orbit + '0.3)';
  ctx.lineWidth = 1;
  ctx.setLineDash([4, 8]);
  ctx.beginPath();
  ctx.arc(CX, CY, 80, 0, Math.PI * 2);
  ctx.stroke();
  ctx.setLineDash([]);
  ctx.globalAlpha = alpha * 0.15;
  ctx.fillStyle = '#4af0c8';
  ctx.beginPath();
  ctx.arc(CX, CY, 10, 0, Math.PI * 2);
  ctx.fill();
  ctx.globalAlpha = 1;
}

function drawDemokrit(alpha, t) {
  const wobble = Math.sin(t * 0.03) * 5;
  // glowing sphere
  ctx.globalAlpha = alpha;
  const grd = ctx.createRadialGradient(CX - 18, CY - 18, 8, CX, CY, 80 + wobble);
  grd.addColorStop(0, 'rgba(200,220,255,0.9)');
  grd.addColorStop(0.4, 'rgba(120,160,255,0.6)');
  grd.addColorStop(1, 'rgba(40,80,180,0.1)');
  ctx.fillStyle = grd;
  ctx.beginPath();
  ctx.arc(CX, CY, 80 + wobble, 0, Math.PI * 2);
  ctx.fill();
  // rim
  ctx.strokeStyle = 'rgba(180,200,255,0.5)';
  ctx.lineWidth = 2;
  ctx.stroke();
  // label inside
  ctx.globalAlpha = alpha * 0.6;
  ctx.fillStyle = '#e8eaf6';
  ctx.font = 'italic 13px Playfair Display, serif';
  ctx.textAlign = 'center';
  ctx.fillText('ἄτομος', CX, CY + 5);
  ctx.globalAlpha = 1;
}

function drawDalton(alpha, t) {
  ctx.globalAlpha = alpha;
  const r = 68 + Math.sin(t * 0.02) * 3;
  // solid ball
  const grd = ctx.createRadialGradient(CX - 20, CY - 20, 5, CX, CY, r);
  grd.addColorStop(0, '#ffe8a0');
  grd.addColorStop(0.5, '#f5c842');
  grd.addColorStop(1, '#a07010');
  ctx.fillStyle = grd;
  ctx.beginPath();
  ctx.arc(CX, CY, r, 0, Math.PI * 2);
  ctx.fill();
  ctx.strokeStyle = 'rgba(255,220,100,0.4)';
  ctx.lineWidth = 2;
  ctx.stroke();
  ctx.globalAlpha = 1;
}

function drawThomson(alpha, t) {
  ctx.globalAlpha = alpha;
  // positive blob
  const grd = ctx.createRadialGradient(CX, CY, 10, CX, CY, 85);
  grd.addColorStop(0, 'rgba(255,160,80,0.85)');
  grd.addColorStop(0.7, 'rgba(255,100,50,0.4)');
  grd.addColorStop(1, 'rgba(180,40,10,0.05)');
  ctx.fillStyle = grd;
  ctx.beginPath();
  ctx.arc(CX, CY, 85, 0, Math.PI * 2);
  ctx.fill();
  // electrons (rosinen)
  const ePos = [
    { r: 30, a: 0.5 }, { r: 55, a: 2.1 }, { r: 40, a: 3.8 },
    { r: 65, a: 1.2 }, { r: 25, a: 4.5 }, { r: 70, a: 5.8 }
  ];
  ePos.forEach((ep, i) => {
    const angle = ep.a + t * 0.01 * (i % 2 === 0 ? 1 : -0.7);
    const ex = CX + Math.cos(angle) * ep.r;
    const ey = CY + Math.sin(angle) * ep.r;
    ctx.fillStyle = '#4af0c8';
    ctx.beginPath();
    ctx.arc(ex, ey, 6, 0, Math.PI * 2);
    ctx.fill();
    // glow
    const eg = ctx.createRadialGradient(ex, ey, 1, ex, ey, 10);
    eg.addColorStop(0, 'rgba(74,240,200,0.6)');
    eg.addColorStop(1, 'rgba(74,240,200,0)');
    ctx.fillStyle = eg;
    ctx.beginPath();
    ctx.arc(ex, ey, 10, 0, Math.PI * 2);
    ctx.fill();
  });
  ctx.globalAlpha = 1;
}

function drawRutherford(alpha, t) {
  ctx.globalAlpha = alpha;
  // orbits
  const orbits = [{ rx: 100, ry: 30, angle: 0 }, { rx: 90, ry: 32, angle: 1.1 }, { rx: 110, ry: 25, angle: -0.7 }];
  orbits.forEach((orb, i) => {
    ctx.save();
    ctx.translate(CX, CY);
    ctx.rotate(orb.angle);
    ctx.strokeStyle = `rgba(74,240,200,${0.25 * alpha})`;
    ctx.lineWidth = 1;
    ctx.setLineDash([4, 5]);
    ctx.beginPath();
    ctx.ellipse(0, 0, orb.rx, orb.ry, 0, 0, Math.PI * 2);
    ctx.stroke();
    ctx.setLineDash([]);
    // electron on orbit
    const ea = t * 0.025 * (i === 1 ? -1 : 1) + (i * 2.1);
    const ex = Math.cos(ea) * orb.rx;
    const ey = Math.sin(ea) * orb.ry;
    ctx.fillStyle = '#4af0c8';
    ctx.beginPath();
    ctx.arc(ex, ey, 5, 0, Math.PI * 2);
    ctx.fill();
    ctx.restore();
  });
  // nucleus
  const ng = ctx.createRadialGradient(CX - 4, CY - 4, 1, CX, CY, 14);
  ng.addColorStop(0, '#fff');
  ng.addColorStop(0.3, '#f5c842');
  ng.addColorStop(1, '#c08010');
  ctx.fillStyle = ng;
  ctx.beginPath();
  ctx.arc(CX, CY, 12, 0, Math.PI * 2);
  ctx.fill();
  ctx.globalAlpha = 1;
}

function drawNeutron(alpha, t) {
  ctx.globalAlpha = alpha;
  // same orbits as rutherford but nucleus shows protons + neutrons
  const orbits = [{ rx: 95, ry: 28, angle: 0.2 }, { rx: 85, ry: 35, angle: 1.3 }, { rx: 108, ry: 22, angle: -0.5 }];
  orbits.forEach((orb, i) => {
    ctx.save();
    ctx.translate(CX, CY);
    ctx.rotate(orb.angle);
    ctx.strokeStyle = `rgba(74,240,200,${0.25 * alpha})`;
    ctx.lineWidth = 1;
    ctx.setLineDash([4, 5]);
    ctx.beginPath();
    ctx.ellipse(0, 0, orb.rx, orb.ry, 0, 0, Math.PI * 2);
    ctx.stroke();
    ctx.setLineDash([]);
    const ea = t * 0.025 * (i === 1 ? -1 : 1) + (i * 2.1);
    const ex = Math.cos(ea) * orb.rx;
    const ey = Math.sin(ea) * orb.ry;
    ctx.fillStyle = '#4af0c8';
    ctx.beginPath();
    ctx.arc(ex, ey, 5, 0, Math.PI * 2);
    ctx.fill();
    ctx.restore();
  });
  // nucleus with protons & neutrons
  const nucleons = [
    { x: -5, y: -5, type: 'p' }, { x: 6, y: -3, type: 'n' },
    { x: 0, y: 7, type: 'p' }, { x: -6, y: 4, type: 'n' },
    { x: 5, y: 5, type: 'p' }
  ];
  nucleons.forEach(n => {
    ctx.fillStyle = n.type === 'p' ? '#ff5c5c' : '#8080ff';
    ctx.beginPath();
    ctx.arc(CX + n.x, CY + n.y, 5, 0, Math.PI * 2);
    ctx.fill();
  });
  ctx.globalAlpha = 1;
}

function drawBohr(alpha, t) {
  ctx.globalAlpha = alpha;
  const shells = [
    { r: 45, electrons: 2, color: '#4af0c8', speed: 0.04 },
    { r: 80, electrons: 3, color: '#e05aff', speed: 0.025 },
    { r: 120, electrons: 2, color: '#4af0c8', speed: 0.015 },
  ];
  shells.forEach((sh, si) => {
    // orbit ring
    ctx.strokeStyle = `rgba(255,255,255,${0.12 * alpha})`;
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.arc(CX, CY, sh.r, 0, Math.PI * 2);
    ctx.stroke();
    // electrons
    for (let e = 0; e < sh.electrons; e++) {
      const angle = t * sh.speed + (e / sh.electrons) * Math.PI * 2;
      const ex = CX + Math.cos(angle) * sh.r;
      const ey = CY + Math.sin(angle) * sh.r;
      // trail
      for (let tr = 1; tr <= 6; tr++) {
        const ta = angle - tr * 0.12;
        const tx = CX + Math.cos(ta) * sh.r;
        const ty = CY + Math.sin(ta) * sh.r;
        ctx.fillStyle = sh.color;
        ctx.globalAlpha = alpha * (0.06 * (7 - tr) / 7);
        ctx.beginPath();
        ctx.arc(tx, ty, 4, 0, Math.PI * 2);
        ctx.fill();
      }
      ctx.globalAlpha = alpha;
      ctx.fillStyle = sh.color;
      ctx.beginPath();
      ctx.arc(ex, ey, 5.5, 0, Math.PI * 2);
      ctx.fill();
    }
  });
  // nucleus
  const ng = ctx.createRadialGradient(CX - 4, CY - 4, 1, CX, CY, 16);
  ng.addColorStop(0, '#fff');
  ng.addColorStop(0.3, '#f5c842');
  ng.addColorStop(1, '#c08010');
  ctx.globalAlpha = alpha;
  ctx.fillStyle = ng;
  ctx.beginPath();
  ctx.arc(CX, CY, 14, 0, Math.PI * 2);
  ctx.fill();
  ctx.globalAlpha = 1;
}

function drawQuantum(alpha, t) {
  ctx.globalAlpha = alpha;
  // probability cloud – multiple gaussian blobs
  const cloudData = [];
  const N = 3000;
  // s orbital (sphere)
  for (let i = 0; i < N * 0.3; i++) {
    const r = Math.abs(gaussRand()) * 40 + Math.abs(gaussRand()) * 20;
    const th = Math.random() * Math.PI * 2;
    cloudData.push({ x: CX + Math.cos(th) * r, y: CY + Math.sin(th) * r, c: 0 });
  }
  // p orbital (dumbbell)
  for (let i = 0; i < N * 0.4; i++) {
    const side = Math.random() > 0.5 ? 1 : -1;
    const dist = 60 + Math.abs(gaussRand()) * 30;
    const spread = gaussRand() * 18;
    const a = t * 0.005;
    cloudData.push({
      x: CX + Math.cos(a) * dist * side - Math.sin(a) * spread,
      y: CY + Math.sin(a) * dist * side + Math.cos(a) * spread,
      c: 1
    });
  }
  // d orbital (complex)
  for (let i = 0; i < N * 0.3; i++) {
    const angle = Math.random() * Math.PI * 2;
    const r = 90 + gaussRand() * 20;
    const th = gaussRand() * 0.4;
    cloudData.push({
      x: CX + Math.cos(angle + th) * r * 0.9,
      y: CY + Math.sin(angle) * r * 0.3 + gaussRand() * 15,
      c: 2
    });
  }

  cloudData.forEach(pt => {
    const colors = ['rgba(74,240,200,', 'rgba(224,90,255,', 'rgba(245,200,66,'];
    ctx.fillStyle = colors[pt.c] + (0.025 * alpha) + ')';
    ctx.fillRect(pt.x, pt.y, 1.5, 1.5);
  });

  // nucleus
  const ng = ctx.createRadialGradient(CX, CY, 0, CX, CY, 8);
  ng.addColorStop(0, '#fff');
  ng.addColorStop(0.5, '#f5c842');
  ng.addColorStop(1, 'rgba(200,120,0,0.3)');
  ctx.globalAlpha = alpha;
  ctx.fillStyle = ng;
  ctx.beginPath();
  ctx.arc(CX, CY, 8, 0, Math.PI * 2);
  ctx.fill();
  ctx.globalAlpha = 1;
}

function gaussRand() {
  let u = 0, v = 0;
  while (u === 0) u = Math.random();
  while (v === 0) v = Math.random();
  return Math.sqrt(-2.0 * Math.log(u)) * Math.cos(2.0 * Math.PI * v);
}

const drawFns = {
  none: drawNone,
  demokrit: drawDemokrit,
  dalton: drawDalton,
  thomson: drawThomson,
  rutherford: drawRutherford,
  neutron: drawNeutron,
  bohr: drawBohr,
  quantum: drawQuantum,
};

// ─── MAIN LOOP ────────────────────────────────
function render() {
  ctx.clearRect(0, 0, W, H);
  animT++;

  // grid bg
  ctx.strokeStyle = 'rgba(74,240,200,0.04)';
  ctx.lineWidth = 1;
  for (let x = 0; x < W; x += 30) {
    ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, H); ctx.stroke();
  }
  for (let y = 0; y < H; y += 30) {
    ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(W, y); ctx.stroke();
  }

  if (transitioning) {
    transitionT++;
    const p = Math.min(transitionT / TRANSITION_DURATION, 1);
    const ease = p < 0.5 ? 2 * p * p : -1 + (4 - 2 * p) * p;

    // fade out old, fade in new
    const alphaOut = 1 - ease;
    const alphaIn = ease;

    if (drawFns[fromType]) drawFns[fromType](alphaOut, animT);
    if (drawFns[toType]) drawFns[toType](alphaIn, animT);

    if (p >= 1) transitioning = false;
  } else {
    if (drawFns[toType]) drawFns[toType](1.0, animT);
  }
  particles = particles.filter(p => p.life > 0);
  particles.forEach(p => {
    p.update();
    p.draw(ctx);
  });

  energyWaves = energyWaves.filter(w => w.alpha > 0);
  energyWaves.forEach(w => {
    w.r += 3;
    w.alpha -= 0.015;
    
    ctx.globalAlpha = w.alpha * 0.3;
    ctx.strokeStyle = '#4af0c8';
    ctx.lineWidth = 2;
    ctx.setLineDash([5, 5]);
    ctx.beginPath();
    ctx.arc(CX, CY, w.r, 0, Math.PI * 2);
    ctx.stroke();
    ctx.setLineDash([]);
  });
  ctx.globalAlpha = 1;

  requestAnimationFrame(render);
}

// Partikel-System
let particles = [];

class Particle {
  constructor(x, y, vx, vy, color) {
    this.x = x;
    this.y = y;
    this.vx = vx;
    this.vy = vy;
    this.color = color;
    this.life = 1.0;
    this.size = Math.random() * 3 + 1;
  }
  
  update() {
    this.x += this.vx;
    this.y += this.vy;
    this.vy += 0.1; // Gravitation
    this.life -= 0.02;
  }
  
  draw(ctx) {
    ctx.globalAlpha = this.life;
    ctx.fillStyle = this.color;
    ctx.beginPath();
    ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
    ctx.fill();
  }
}

function createTransitionParticles(fromType, toType) {
  const colors = ['#4af0c8', '#e05aff', '#f5c842', '#ff5c5c'];
  for (let i = 0; i < 50; i++) {
    const angle = Math.random() * Math.PI * 2;
    const speed = Math.random() * 3 + 1;
    particles.push(new Particle(
      CX, CY,
      Math.cos(angle) * speed,
      Math.sin(angle) * speed,
      colors[Math.floor(Math.random() * colors.length)]
    ));
  }
}


let energyWaves = [];

function createEnergyWave() {
  energyWaves.push({ r: 0, alpha: 1.0 });
}

// Alle 2 Sekunden eine Welle
setInterval(createEnergyWave, 2000);


// init
transitionAtom('none');
document.getElementById('atom-label').textContent = atomConfigs['none']?.label || '';
goTo(0);
render();
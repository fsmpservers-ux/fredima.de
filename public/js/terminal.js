(function(){
const h = \`
<div id=term style=display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.95);z-index:9999;font-family:'Courier New',monospace;padding:20px;box-sizing:border-box;>
<div id=out style=color:#0f0;height:calc(100% - 40px);overflow-y:auto;font-size:14px;line-height:1.6;><div>Fredima Terminal v1.0.0</div><div>Type 'help' for commands.</div><br></div>
<div style=display:flex;align-items:center;><span style=color:#00d4ff;>fredi@fredima.de:~$\u003c/span>
<input id=inp type=text autocomplete=off spellcheck=false style=flex:1;background:transparent;border:none;color:#0f0;font-family:inherit;font-size:14px;outline:none;margin-left:8px;></div></div>
\`;
const d = document.createElement('div');
d.innerHTML = h;
document.body.appendChild(d.firstElementChild);
const o = document.getElementById('term'), out = document.getElementById('out'), inp = document.getElementById('inp');
let open = false, hist = [], idx = -1;
function toggle(){open=!open;o.style.display=open?'block':'none';if(open){inp.focus();out.scrollTop=out.scrollHeight;}}
document.addEventListener('keydown',e=>{
if(e.key=='\`'||(e.ctrlKey&&e.shiftKey&&e.key=='T')){e.preventDefault();toggle();}
if(e.key=='Escape'&&open)toggle();
});
const cmds = {
help:()=>\`Available commands:
  help      - Show this help
  clear     - Clear terminal
  date      - Current date/time
  whoami    - Visitor info
  neofetch  - System info
  stats     - Website statistics
  github    - GitHub profile
  motd      - Random message
  reboot    - Reload page
  exit      - Close terminal\`,
clear:()=>{out.innerHTML='';return'';},
date:()=>new Date().toString(),
whoami:()=>\`Visitor Info:
  Browser: \${navigator.appName}
  Platform: \${navigator.platform}
  Language: \${navigator.language}
  Screen: \${window.screen.width}x\${window.screen.height}\`,
neofetch:async()=>{const b=/Edg/.test(navigator.userAgent)?'Edge':/Chrome/.test(navigator.userAgent)?'Chrome':/Safari/.test(navigator.userAgent)?'Safari':/Firefox/.test(navigator.userAgent)?'Firefox':'Unknown';return\`
    _______________                  fredi@fredima.de
   /               \\\\                 ------------------
  |  👾  ╭──────╮  |                 OS: \${navigator.platform}
  |      | ͡° ͜ʖ ͡° |  |                 Browser: \${b}
  |  🎮  ╰──────╯  |                 Resolution: \${window.screen.width}x\${window.screen.height}
   \\\\_______________/                 Language: \${navigator.language}
                                     Timezone: \${Intl.DateTimeFormat().resolvedOptions().timeZone}\`;},
stats:async()=>{try{const r=await fetch('/api/stats.php'),j=await r.json();return\`Stats:
  Total: \${j.total||0}
  Today: \${Object.values(j.daily||{}).pop()||0}\`;}catch(e){return'Error';}},
github:async()=>{try{const r=await fetch('/api/github.php'),j=await r.json();let s=\`GitHub: @\${j.username||'fredima2x'}\\n\\n\`;j.repos?.slice(0,5).forEach(r=>{s+=\`  ⭐ \${r.stars}  \${r.name} (\${r.lang||'-'})
\`;});return s;}catch(e){return'Error fetching GitHub';}},
motd:()=>{const m=['Keep it simple','There is no place like 127.0.0.1','Hello World!','rm -rf /','It works on my machine','\\\\(OoO)/','Hakuna Matata','sudo make me a sandwich'];return\`MOTD: "\${m[Math.floor(Math.random()*m.length)]}"\`;},
reboot:()=>{setTimeout(()=>location.reload(),500);return'Rebooting...';},
exit:()=>{setTimeout(toggle,100);return'Goodbye!';}
};
function a(t){const e=document.createElement('div');e.textContent=t;e.style.whiteSpace='pre-wrap';out.appendChild(e);out.appendChild(document.createElement('br'));}
inp.addEventListener('keydown',async e=>{
if(e.key=='Enter'){const c=inp.value.trim();if(!c)return;hist.push(c);idx=hist.length;a(\`fredi@fredima.de:~$ \${c}\`);const f=cmds[c.split(' ')[0]];let r;if(f){try{r=await f();}catch(x){r='Error';}}else{r=\`Not found: \${c}. Type 'help'.\`;}if(r)a(r);inp.value='';out.scrollTop=out.scrollHeight;}
if(e.key=='ArrowUp'){e.preventDefault();if(idx>0)inp.value=hist[--idx]||'';}
if(e.key=='ArrowDown'){e.preventDefault();if(idx<hist.length-1)inp.value=hist[++idx]||'';else{idx=hist.length;inp.value='';}}
});
o.addEventListener('click',e=>{if(e.target===o)toggle();});
console.log('Terminal: Press \` to open');
})();

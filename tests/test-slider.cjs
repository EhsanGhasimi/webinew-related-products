const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
function setup(reduce=false) {
 const handlers={}, toggleHandlers={}, cards=[{},{},{}], toggle={textContent:'',setAttribute(){},addEventListener(k,f){toggleHandlers[k]=f;}}, status={setAttribute(){}};
 const steps=[-1,1].map(n=>({dataset:{step:String(n)},addEventListener(k,f){this.click=f;}}));
 const controls={querySelector(){return toggle;},querySelectorAll(){return steps;}};
 const slider={dataset:{},isConnected:true,querySelectorAll(){return cards;},querySelector(s){return s==='.wrp-controls'?controls:status;},addEventListener(k,f){handlers[k]=f;},contains(){return false;}};
 const doc={readyState:'complete',hidden:false,querySelectorAll(){return [slider];}};
 let tick;
 vm.runInNewContext(fs.readFileSync(require('path').join(__dirname, '../assets/slider.js'),'utf8'),{document:doc,window:{matchMedia(){return {matches:reduce};},clearInterval(){},setInterval(f,ms){assert.equal(ms,5000);tick=f;return 1;}}});
 return {cards,handlers,toggleHandlers,doc,steps,tick:()=>tick()};
}
let s=setup();
assert.equal(s.cards[0].hidden,false); s.tick(); assert.equal(s.cards[1].hidden,false);
s.handlers.mouseenter();s.tick();assert.equal(s.cards[1].hidden,false);
s.handlers.mouseleave();s.tick();assert.equal(s.cards[2].hidden,false);
s.tick();assert.equal(s.cards[0].hidden,false);
s.toggleHandlers.click();s.tick();assert.equal(s.cards[0].hidden,false);
s.toggleHandlers.click();s.tick();assert.equal(s.cards[1].hidden,false);
s.doc.hidden=true;s.tick();assert.equal(s.cards[1].hidden,false);
s.doc.hidden=false;s.handlers.focusin();s.tick();assert.equal(s.cards[1].hidden,false);
s.handlers.focusout({relatedTarget:null});s.tick();assert.equal(s.cards[2].hidden,false);
s.steps[1].click();assert.equal(s.cards[0].hidden,false);
s=setup(true);s.tick();assert.equal(s.cards[0].hidden,false);
s.toggleHandlers.click();s.tick();assert.equal(s.cards[1].hidden,false);
console.log('PASS autoplay, wraparound, hover, focus, pause/resume, hidden tab, manual next, reduced motion');

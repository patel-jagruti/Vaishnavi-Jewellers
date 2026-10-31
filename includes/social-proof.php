<!-- ===== SOCIAL PROOF NOTIFICATIONS (Feature A) ===== -->
<style>
.sp-toast {
  position: fixed;
  bottom: 100px;
  left: 20px;
  z-index: 88888;
  display: flex;
  align-items: center;
  gap: 12px;
  background: #fff;
  border-radius: 14px;
  padding: 14px 18px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.12);
  border-left: 4px solid #D4AF37;
  max-width: 310px;
  transform: translateX(-360px);
  transition: transform 0.5s cubic-bezier(0.16,1,0.3,1);
  font-family: 'Poppins', sans-serif;
  pointer-events: none;
}
.sp-toast.show { transform: translateX(0); }
.sp-toast-avatar {
  width: 42px; height: 42px;
  border-radius: 50%;
  background: linear-gradient(135deg, #D4AF37, #F5E6A3);
  display: flex; align-items: center; justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}
.sp-toast-text { flex: 1; }
.sp-toast-name { font-size: 12px; font-weight: 700; color: #1A1A1A; margin-bottom: 2px; }
.sp-toast-msg  { font-size: 11px; color: #666; line-height: 1.4; }
.sp-toast-time { font-size: 9px; color: #aaa; margin-top: 3px; }
.sp-live-dot {
  position: fixed;
  bottom: 108px;
  left: 308px;
  z-index: 88889;
  width: 8px; height: 8px;
  background: #22c55e;
  border-radius: 50%;
  display: none;
  box-shadow: 0 0 0 0 rgba(34,197,94,0.4);
  animation: livePulse 1.5s infinite;
}
@keyframes livePulse {
  0%   { box-shadow: 0 0 0 0 rgba(34,197,94,0.4); }
  70%  { box-shadow: 0 0 0 8px rgba(34,197,94,0); }
  100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
}
</style>

<div class="sp-toast" id="spToast">
  <div class="sp-toast-avatar" id="spAvatar">💍</div>
  <div class="sp-toast-text">
    <div class="sp-toast-name" id="spName">Priya S.</div>
    <div class="sp-toast-msg" id="spMsg">just purchased a Diamond Solitaire Ring</div>
    <div class="sp-toast-time" id="spTime">2 minutes ago · Mumbai</div>
  </div>
</div>
<div class="sp-live-dot" id="spLiveDot"></div>

<script>
(function() {
  const notifications = [
    { name:'Priya S.',   city:'Mumbai',   emoji:'💍', action:'just purchased',  item:'Diamond Solitaire Ring',       time:'2 min ago' },
    { name:'Meena R.',   city:'Surat',    emoji:'📿', action:'added to wishlist', item:'22K Gold Mangalsutra',        time:'5 min ago' },
    { name:'Kavya P.',   city:'Ahmedabad',emoji:'💎', action:'is viewing',       item:'Emerald Bridal Necklace Set', time:'Just now'  },
    { name:'Anita D.',   city:'Delhi',    emoji:'✨', action:'just purchased',  item:'Pearl Drop Earrings',          time:'8 min ago' },
    { name:'Rekha M.',   city:'Baroda',   emoji:'💛', action:'just purchased',  item:'18K Gold Bangles (Set of 6)', time:'12 min ago'},
    { name:'Sunita K.',  city:'Rajkot',   emoji:'🌸', action:'added to wishlist','item':'Ruby Pendant Necklace',      time:'3 min ago' },
    { name:'Geeta V.',   city:'Jaipur',   emoji:'💠', action:'is viewing',       item:'Sapphire Tennis Bracelet',    time:'Just now'  },
    { name:'Hina B.',    city:'Hyderabad',emoji:'🏅', action:'just purchased',  item:'Gold Coin (24K, 5g)',          time:'18 min ago'},
  ];

  let idx = 0;
  const toast   = document.getElementById('spToast');
  const avatar  = document.getElementById('spAvatar');
  const nameEl  = document.getElementById('spName');
  const msgEl   = document.getElementById('spMsg');
  const timeEl  = document.getElementById('spTime');
  const liveDot = document.getElementById('spLiveDot');

  function showNext() {
    const n = notifications[idx % notifications.length];
    idx++;

    avatar.textContent = n.emoji;
    nameEl.textContent = n.name;
    msgEl.textContent  = n.action + ' ' + n.item;
    timeEl.textContent = n.time + ' · ' + n.city;

    toast.classList.add('show');
    liveDot.style.display = 'block';

    setTimeout(() => {
      toast.classList.remove('show');
      liveDot.style.display = 'none';
    }, 5000);
  }

  // First show after 8 seconds, then every 15 seconds
  setTimeout(() => {
    showNext();
    setInterval(showNext, 15000);
  }, 8000);
})();
</script>

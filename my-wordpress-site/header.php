<?php // footer.php — v5.0 ?>
  <footer>
    <p>&copy; 2025 NeuroTechGuide • Ad-Free • <a href="/privacy">Privacy</a> • <a href="/contact">Contact</a> • <a href="#top" onclick="window.scrollTo(0,0);">Scroll to Top</a></p>
  </footer>
  <script>
    // Theme Toggle + Search Autocomplete
    function toggleTheme() { const isLight = document.body.getAttribute('data-theme') === 'light'; const newTheme = isLight ? 'dark' : 'light'; document.body.setAttribute('data-theme', newTheme); localStorage.setItem('theme', newTheme); fetch('?theme=' + newTheme, {method:'HEAD'}); }
    if (localStorage.getItem('theme') === 'light') document.body.setAttribute('data-theme', 'light');
    const searchData = ['neuralink','ethics','eeg','bci','implants','news']; document.getElementById('search').addEventListener('input', e => { const term = e.target.value.toLowerCase(); const results = document.getElementById('results'); if (!term) return results.innerHTML = ''; const matches = searchData.filter(s => s.includes(term)); results.innerHTML = matches.map(m => `<a href="?search=${m}" style="display:block;padding:0.5rem;color:var(--accent);">${m}</a>`).join(''); });
    function validateEmail() { const email = document.getElementById('email').value; if (!email.includes('@') || !email.includes('.')) { alert('Valid email required'); return false; } return true; }
    function share(url) { if (navigator.share) navigator.share({url}); else prompt('Share:', url); }
  </script>
  <script> if ('serviceWorker' in navigator) window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js')); </script>
</body>
</html>
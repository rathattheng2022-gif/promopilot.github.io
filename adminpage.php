<!DOCTYPE html>
<html lang="en">
<head><?php include 'head.php'; ?>
<link rel="stylesheet" href="admin.css" />
</head>
<body>
  <header class="page-header">
    <div class="logo-badge">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="#10c875" aria-hidden="true"><path d="M13 2 4.5 13H11l-1 9L21 11h-7z"/></svg>
      <span>PROMOPILOT</span>
    </div>
    <h1>Add discount code</h1>
    <p>Fill in the details to list a new deal</p>
  </header>

  <main>
    <div class="coupon-card" id="couponCard">
      <div class="coupon-top">
        <div class="ribbon">
          <span class="ribbon-label">New Deal</span>
        </div>
        <div class="form-area">
          <div class="form-grid">
            <div class="form-group">
              <label for="pp-company">Company name</label>
              <input type="text" id="pp-company" placeholder="e.g. Wownow" autocomplete="off">
            </div>

            <div class="form-group">
              <label for="pp-discount">Discount amount</label>
              <input type="text" id="pp-discount" placeholder="e.g. 30% or $25" autocomplete="off">
            </div>

            <div class="form-group">
              <label for="pp-code">Discount code</label>
              <input type="text" id="pp-code" placeholder="e.g. JUMBO30" autocomplete="off">
            </div>

            <div class="form-group">
              <label for="pp-expiry">Expiration date</label>
              <input type="date" id="pp-expiry">
            </div>
          </div>
        </div>
      </div>

      <div class="perforation">
        <div class="notch notch-l"></div>
        <div class="perf-line"></div>
        <div class="notch notch-r"></div>
      </div>

      <div class="coupon-bottom">
        <div class="deal-badge">
          <svg width="15" height="15" viewBox="0 0 24 24" stroke="#10c875" stroke-width="2" fill="none" aria-hidden="true">
            <path d="M12 3 4 7v5c0 4.4 3.4 8.6 8 9 4.6-.4 8-4.6 8-9V7z"/>
            <polyline points="9 12 11 14 15 10"/>
          </svg>
          <span>Verified &amp; active</span>
        </div>

        <button class="btn-add" id="addBtn" onclick="handleAdd()">
          <svg width="18" height="18" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" fill="none" aria-hidden="true">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Add deal
        </button>
      </div>

      <div class="success-overlay" id="successOverlay" role="status" aria-live="polite">
        <div class="success-icon">
    
          <svg width="32" height="32" viewBox="0 0 24 24" stroke="#040d06" stroke-width="2.5" fill="none" aria-hidden="true">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
        </div>
        <div class="success-title">Added successfully!</div>
        <p class="success-sub" id="successSub"></p>
        <button class="btn-reset" onclick="handleReset()">Add another deal</button>
      </div>
    </div>
  </main>

  <script>
    function handleAdd() {
      const ids = ['pp-company', 'pp-discount', 'pp-code', 'pp-expiry'];
      const vals = ids.map(id => document.getElementById(id).value.trim());
      let valid = true;
 
      ids.forEach((id, i) => {
        const el = document.getElementById(id);
        el.classList.remove('error', 'shake');
        void el.offsetWidth; 
        if (!vals[i]) {
          el.classList.add('error', 'shake');
          setTimeout(() => el.classList.remove('shake', 'error'), 500);
          valid = false;
        }
      });
 
      if (!valid) return;
 
      const [company, discount, code, expiry] = vals;
      const date = new Date(expiry + 'T00:00:00').toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric'
      });
 
      document.getElementById('successSub').textContent =
        `${company} · ${discount} off · Code: ${code.toUpperCase()} · Expires ${date}`;
 
      document.getElementById('successOverlay').classList.add('show');
    }
 
    function handleReset() {
      ['pp-company', 'pp-discount', 'pp-code', 'pp-expiry'].forEach(id => {
        document.getElementById(id).value = '';
      });
      document.getElementById('successOverlay').classList.remove('show');
    }
  </script>

</body>
</html>
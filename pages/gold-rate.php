<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding:40px 0 60px;">
  <div class="section-head">
    <h1>Gold Rate Calculator</h1>
    <p>Calculate the value of your gold investment</p>
  </div>

  <div class="grid-2" style="max-width:900px; margin:0 auto;">
    <div class="category" style="padding:24px;">
      <h3 style="margin-bottom:16px;">Investment Calculator</h3>

      <div style="display:flex; flex-direction:column; gap:16px;">
        <div>
          <label class="meta">Gold Purity</label>
          <select id="goldPurity" class="input" style="width:100%;">
            <option value="24">24K (99.9% Pure)</option>
            <option value="22">22K (91.6% Pure)</option>
            <option value="18">18K (75% Pure)</option>
          </select>
        </div>

        <div>
          <label class="meta">Weight (grams)</label>
          <input type="number" id="goldWeight" class="input" value="10" min="0.5" step="0.5" style="width:100%;">
        </div>

        <div>
          <label class="meta">Current Gold Rate (per gram)</label>
          <input type="number" id="goldRate" class="input" value="7250" min="0" step="10" style="width:100%;">
          <div class="meta" style="margin-top:4px;">Live rate may vary. Last updated: <?= date('d M Y, h:i A') ?></div>
        </div>

        <button type="button" class="btn btn-primary" onclick="calculateGold()">Calculate Value</button>
      </div>
    </div>

    <div class="category" style="padding:24px; background:linear-gradient(135deg, #7A1F2A 0%, #4a1520 100%);">
      <h3 style="margin-bottom:16px; color:#D4AF37;">Your Investment</h3>

      <div id="result" style="display:flex; flex-direction:column; gap:16px;">
        <div>
          <div class="meta" style="color:rgba(255,255,255,0.7);">Pure Gold Value</div>
          <div class="price" style="font-size:28px; color:#FAF9F6;">₹72,500</div>
        </div>

        <div>
          <div class="meta" style="color:rgba(255,255,255,0.7);">Making Charges (8%)</div>
          <div style="font-size:20px; color:#FAF9F6;">₹5,800</div>
        </div>

        <div style="border-top:1px solid rgba(255,255,255,0.2); padding-top:16px;">
          <div class="meta" style="color:rgba(255,255,255,0.7);">Total Value</div>
          <div class="price" style="font-size:36px; color:#D4AF37;">₹78,300</div>
        </div>

        <div class="meta" style="color:rgba(255,255,255,0.6); margin-top:8px;">
          *Including GST of 3%. Exchange value may differ.
        </div>
      </div>
    </div>
  </div>

  <div class="category" style="max-width:900px; margin:24px auto 0; padding:24px;">
    <h3>Gold Purity Guide</h3>
    <div class="grid-3" style="margin-top:16px; gap:16px;">
      <div style="padding:16px; background:#FAF9F6;">
        <div class="price">24K</div>
        <div>99.9% Pure</div>
        <div class="meta">Investment Grade</div>
      </div>
      <div style="padding:16px; background:#FAF9F6;">
        <div class="price">22K</div>
        <div>91.6% Pure</div>
        <div class="meta">Jewellery Grade</div>
      </div>
      <div style="padding:16px; background:#FAF9F6;">
        <div class="price">18K</div>
        <div>75% Pure</div>
        <div class="meta">Fashion Grade</div>
      </div>
    </div>
  </div>
</div>

<script>
function calculateGold() {
  const purity = parseInt(document.getElementById('goldPurity').value);
  const weight = parseFloat(document.getElementById('goldWeight').value) || 0;
  const rate = parseFloat(document.getElementById('goldRate').value) || 0;

  const makingCharge = 0.08;
  const gst = 0.03;

  const pureValue = weight * rate;
  const making = pureValue * makingCharge;
  const withMaking = pureValue + making;
  const withGst = withMaking * (1 + gst);
  const total = Math.round(withGst);

  const html = `
    <div>
      <div class="meta" style="color:rgba(255,255,255,0.7);">Pure Gold Value</div>
      <div class="price" style="font-size:28px; color:#FAF9F6;">₹${pureValue.toLocaleString()}</div>
    </div>
    <div>
      <div class="meta" style="color:rgba(255,255,255,0.7);">Making Charges (8%)</div>
      <div style="font-size:20px; color:#FAF9F6;">₹${Math.round(making).toLocaleString()}</div>
    </div>
    <div style="border-top:1px solid rgba(255,255,255,0.2); padding-top:16px;">
      <div class="meta" style="color:rgba(255,255,255,0.7);">Total Value</div>
      <div class="price" style="font-size:36px; color:#D4AF37;">₹${total.toLocaleString()}</div>
    </div>
    <div class="meta" style="color:rgba(255,255,255,0.6); margin-top:8px;">
      *Including GST of 3%. Exchange value may differ.
    </div>
  `;

  document.getElementById('result').innerHTML = html;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
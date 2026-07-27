// Writes one self-contained HTML file per QR-receive outcome screen (sample data only).
// Each is rendered at a fixed 700x460 CSS frame so Chrome headless can screenshot it cleanly.
const fs = require("fs");
const path = require("path");
const OUT = __dirname;

const FRAME_W = 700, FRAME_H = 460;

function page(url, cardHtml) {
    return `<!doctype html><html lang="en"><head><meta charset="utf-8">
<style>
  *{box-sizing:border-box}
  html,body{margin:0}
  body{width:${FRAME_W}px;height:${FRAME_H}px;display:grid;place-items:center;
    background:#eef2f0;font-family:"Segoe UI",system-ui,Arial,sans-serif;color:#0e1a15}
  .browser{width:640px;border:1px solid #e4ebe7;border-radius:14px;overflow:hidden;background:#fff;
    box-shadow:0 20px 45px -22px rgba(6,40,28,.4)}
  .bar{display:flex;align-items:center;gap:8px;padding:9px 12px;background:#f8faf9;border-bottom:1px solid #e4ebe7}
  .dots{display:flex;gap:5px}.dots i{width:9px;height:9px;border-radius:50%;background:#d6ddd9;display:inline-block}
  .dots i:nth-child(1){background:#f9a8a4}.dots i:nth-child(2){background:#fcd7a1}.dots i:nth-child(3){background:#a7e0c4}
  .url{flex:1;background:#fff;border:1px solid #e4ebe7;border-radius:7px;font-size:11px;color:#5c6b64;
    padding:4px 10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .body{padding:16px}
  .appcard{border:1px solid #e4ebe7;border-radius:12px;overflow:hidden;background:#fff}
  .head{padding:13px 15px;border-bottom:1px solid #e4ebe7}
  .head.flex{display:flex;align-items:center;gap:11px}
  .head.sb{display:flex;justify-content:space-between;align-items:center;gap:10px}
  .head h4{margin:0;font-size:17px;font-weight:800;letter-spacing:-.01em}
  .head p{margin:3px 0 0;font-size:12px;color:#5c6b64}
  .rows{padding:12px 15px;display:grid;gap:8px}
  .rows.two{grid-template-columns:1fr 1fr}
  .row{display:flex;gap:9px;font-size:12.5px}
  .row dt{color:#8a978f;min-width:96px;flex-shrink:0}
  .row dd{margin:0;color:#0e1a15;font-weight:600}
  .row dd.plain{font-weight:500}
  .acts{padding:0 15px 15px;display:flex;justify-content:flex-end;gap:8px}
  .btn{font-size:12px;font-weight:650;padding:7px 12px;border-radius:9px;border:1px solid transparent;display:inline-flex;align-items:center;gap:6px}
  .btn.primary{background:#059669;color:#fff}
  .btn.ghost{background:#fff;border-color:#e4ebe7;color:#0e1a15}
  .btn.gray{background:#64748b;color:#fff}
  .pill{display:inline-flex;align-items:center;gap:5px;padding:3px 8px;border-radius:7px;font-size:11px;font-weight:700}
  .pill.sky{background:#e0f2fe;color:#1d4ed8}
  .pill.yellow{background:#fef9c3;color:#b45309}
  .pill.red{background:#fee2e2;color:#b42318}
  .pill.mono{background:#f8faf9;color:#0e1a15;border:1px solid #e4ebe7}
  .badge{width:34px;height:34px;border-radius:50%;display:grid;place-items:center;flex-shrink:0;background:#d1fae5;color:#047857}
  .t-ok{color:#047857}.t-red{color:#b42318}.t-amber{color:#b45309}.t-blue{color:#1d4ed8}.t-slate{color:#475569}
  .bigico{display:grid;place-items:center;padding:10px 0 16px}
</style></head><body>
  <div class="browser">
    <div class="bar"><div class="dots"><i></i><i></i><i></i></div>
      <div class="url">dtis.dohwv.ph/qr-receive/${url}</div></div>
    <div class="body">${cardHtml}</div>
  </div>
</body></html>`;
}

const CTRL = "2026-07-000482";

const screens = {
    "01-confirm": page(CTRL, `
      <div class="appcard">
        <div class="head sb">
          <div><h4 class="t-ok">Purchase Request</h4>
            <span class="pill mono" style="margin-top:6px">Control No. ${CTRL}</span></div>
          <span class="btn primary">&#8681; Receive Document</span>
        </div>
        <div class="rows two">
          <div class="row"><dt>Subject:</dt><dd class="plain">Medical supplies &mdash; Q3</dd></div>
          <div class="row"><dt>Status:</dt><dd><span class="pill sky">For Receiving</span></dd></div>
          <div class="row"><dt>Tagging:</dt><dd><span class="pill red">External</span></dd></div>
          <div class="row"><dt>Office:</dt><dd class="t-ok">Accounting Section</dd></div>
        </div>
      </div>`),

    "02-success": page(CTRL, `
      <div class="appcard">
        <div class="head flex">
          <span class="badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg></span>
          <div><h4 class="t-ok">Document Received!</h4><p>Marked as <b>On Process</b> and assigned to your office.</p></div>
        </div>
        <div class="rows">
          <div class="row"><dt>Control No.:</dt><dd><span class="pill mono">${CTRL}</span></dd></div>
          <div class="row"><dt>Subject:</dt><dd>Medical supplies &mdash; Q3</dd></div>
          <div class="row"><dt>Received by:</dt><dd class="t-ok">Accounting Section</dd></div>
          <div class="row"><dt>New Status:</dt><dd><span class="pill yellow">&#8635; On Process</span></dd></div>
        </div>
        <div class="acts"><span class="btn ghost">Dashboard</span><span class="btn primary">View Pending</span></div>
      </div>`),

    "03-wrong-office": page(CTRL, `
      <div class="appcard">
        <div class="head"><h4 class="t-amber">Not Assigned to Your Office</h4>
          <p>This document is addressed to a different office and cannot be received here.</p></div>
        <div class="rows">
          <div class="row"><dt>Control No.:</dt><dd>${CTRL}</dd></div>
          <div class="row"><dt>Subject:</dt><dd class="plain">Medical supplies &mdash; Q3</dd></div>
          <div class="row"><dt>Category:</dt><dd class="plain">Purchase Request</dd></div>
        </div>
        <div class="acts"><span class="btn primary">View Incoming</span></div>
      </div>`),

    "04-already-received": page(CTRL, `
      <div class="appcard">
        <div class="head"><h4 class="t-blue">Already Received</h4>
          <p>This document has already been received and is currently being processed.</p></div>
        <div class="rows">
          <div class="row"><dt>Control No.:</dt><dd>${CTRL}</dd></div>
          <div class="row"><dt>Subject:</dt><dd class="plain">Medical supplies &mdash; Q3</dd></div>
          <div class="row"><dt>Current Status:</dt><dd><span class="pill sky">On Process</span></dd></div>
        </div>
        <div class="acts"><span class="btn ghost">Dashboard</span><span class="btn primary">View Pending</span></div>
      </div>`),

    "05-closed": page("2026-07-000110", `
      <div class="appcard">
        <div class="head"><h4 class="t-slate">Document Closed</h4>
          <p>This document has already been completed and closed.</p></div>
        <div class="rows">
          <div class="row"><dt>Control No.:</dt><dd>2026-07-000110</dd></div>
          <div class="row"><dt>Subject:</dt><dd class="plain">Reimbursement &mdash; travel</dd></div>
          <div class="row"><dt>Category:</dt><dd class="plain">Payment</dd></div>
        </div>
        <div class="acts"><span class="btn gray">Go to Dashboard</span></div>
      </div>`),

    "06-not-found": page("UNKNOWN-CODE", `
      <div class="appcard">
        <div class="head"><h4 class="t-red">Document Not Found</h4>
          <p>The scanned QR code does not match any document in the system.</p></div>
        <div class="bigico">
          <svg viewBox="0 0 24 24" fill="none" stroke="#b42318" stroke-width="1.5" width="52" height="52" style="opacity:.6"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
        </div>
        <div class="acts"><span class="btn gray">Go to Dashboard</span></div>
      </div>`),
};

Object.entries(screens).forEach(([name, html]) => {
    fs.writeFileSync(path.join(OUT, name + ".html"), html);
    console.log("wrote", name + ".html");
});
console.log(`\nFrame: ${FRAME_W}x${FRAME_H}`);

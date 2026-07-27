// DTIS Feature Updates — styled to match the DOH WV CHD Google Slides template.
// Template traits: 16:9 @ 10x5.625in, dark slate background w/ gold waves (template-bg.png),
// aqua accent #64FFDA, near-white #EEFAF6 text, uppercase white titles top-left,
// teal-outlined translucent cards, a URL/label pill bottom-right.
const PptxGenJS = require("pptxgenjs");

const pptx = new PptxGenJS();
pptx.defineLayout({ name: "GS169", width: 10, height: 5.625 });
pptx.layout = "GS169";
pptx.author = "DTIS";
pptx.title = "DTIS — Feature Updates";

const W = 10, H = 5.625;
const BG = "docs/screens/template-bg.png";
const FONT = "Arial";

const C = {
    teal:    "64FFDA",  // aqua accent (from template)
    text:    "EEFAF6",  // near-white body
    white:   "FFFFFF",
    gold:    "D9CF63",  // matches the template's gold waves (UPDATED tag)
    dim:     "9FB3AE",  // muted caption text on dark
    card:    "16262E",  // translucent card fill (used with transparency)
    cardHi:  "1E333C",
    ink:     "0E1A15",
    // status tints for the QR table result cells
    green:   "10B981", amber: "D9A441", blue: "5B9BD5", slate: "7A8C99", red: "E06B6B",
};

// ── Helpers ──────────────────────────────────────────────────────────────────
function newSlide() {
    const slide = pptx.addSlide();
    slide.background = { path: BG };
    return slide;
}

function titleTop(slide, text, opts = {}) {
    slide.addText((opts.raw ? text : text.toUpperCase()), {
        x: 0.5, y: 0.24, w: opts.tw || 8.3, h: 0.72,
        fontSize: opts.size || 30, bold: true, color: C.white, fontFace: FONT,
        align: "left", valign: "middle", charSpacing: 0.5,
    });
    if (opts.sub) {
        slide.addText(opts.sub, {
            x: 0.52, y: 0.98, w: 9, h: 0.32, fontSize: 12, italic: true,
            color: C.teal, fontFace: FONT, align: "left",
        });
    }
    if (opts.tag) tag(slide, opts.tag, 9.6, 0.36);
}

// tag pill anchored by its RIGHT edge (rightX); width sized to the label
function tag(slide, kind, rightX, y) {
    const isNew = kind === "NEW";
    const w = isNew ? 0.85 : 1.36;
    const x = rightX - w;
    slide.addShape(pptx.ShapeType.roundRect, {
        x, y, w, h: 0.4, rectRadius: 0.06,
        fill: { color: isNew ? C.teal : C.gold }, line: { type: "none" },
    });
    slide.addText(kind, {
        x, y, w, h: 0.4, fontSize: 11.5, bold: true, color: "0B1A16",
        fontFace: FONT, align: "center", valign: "middle", wrap: false,
    });
}

// teal-outlined translucent rounded card
function card(slide, x, y, w, h, opts = {}) {
    slide.addShape(pptx.ShapeType.roundRect, {
        x, y, w, h, rectRadius: 0.08,
        fill: { color: opts.fill || C.card, transparency: opts.transparency == null ? 22 : opts.transparency },
        line: { color: opts.line || C.teal, width: opts.lw || 1 },
    });
}

// bottom-right pill (URL / label)
function pill(slide, text) {
    const w = Math.min(4.6, 0.14 * text.length + 0.5);
    slide.addShape(pptx.ShapeType.roundRect, {
        x: W - w - 0.4, y: H - 0.55, w, h: 0.34, rectRadius: 0.17,
        fill: { type: "none" }, line: { color: C.teal, width: 1 },
    });
    slide.addText(text, {
        x: W - w - 0.4, y: H - 0.55, w, h: 0.34, fontSize: 9.5, color: C.text,
        fontFace: FONT, align: "center", valign: "middle",
    });
}

const FOOT = "Document Tracking Information System · DOH WV CHD";

// emphasis runs: [["plain "],["bold",true],...] -> text array with teal bold
function runs(parts, base = 13) {
    return parts.map(([t, em]) => ({
        text: t, options: { fontSize: base, fontFace: FONT, color: em ? C.teal : C.text, bold: !!em },
    }));
}

// numbered teal step
function step(slide, n, text, x, y, w, fs = 13) {
    slide.addShape(pptx.ShapeType.ellipse, { x, y, w: 0.4, h: 0.4, fill: { color: C.teal }, line: { type: "none" } });
    slide.addText(String(n), { x, y, w: 0.4, h: 0.4, fontSize: 14, bold: true, color: "0B1A16", fontFace: FONT, align: "center", valign: "middle" });
    slide.addText(text, { x: x + 0.55, y: y - 0.06, w: w - 0.55, h: 0.52, fontSize: fs, color: C.text, fontFace: FONT, valign: "middle" });
}

// what/why two-card panel
function twoPanel(slide, left, right, y, h) {
    [[left, 0.5], [right, 5.15]].forEach(([p, x]) => {
        card(slide, x, y, 4.35, h);
        slide.addText(p.title, { x: x + 0.22, y: y + 0.14, w: 3.9, h: 0.34, fontSize: 13.5, bold: true, color: C.teal, fontFace: FONT });
        slide.addShape(pptx.ShapeType.line, { x: x + 0.22, y: y + 0.52, w: 3.9, h: 0, line: { color: C.teal, width: 0.75, transparency: 40 } });
        slide.addText(
            p.lines.map((t) => ({ text: t, options: { fontSize: 11, color: C.text, fontFace: FONT, paraSpaceAfter: 6, bullet: { code: "2013", indent: 12 } } })),
            { x: x + 0.24, y: y + 0.62, w: 3.9, h: h - 0.8, valign: "top" }
        );
    });
}

// screenshot aspect 700x460
const SHOT = 460 / 700;
function shot(slide, file, x, y, w, caption, capColor) {
    if (caption) slide.addText(caption, { x, y, w, h: 0.28, fontSize: 11, bold: true, color: capColor || C.teal, fontFace: FONT, align: "center" });
    slide.addImage({ path: "docs/screens/" + file, x, y: y + (caption ? 0.32 : 0), w, h: w * SHOT });
}

// ════════════════════════════════════════════════════════════════════════════
// 1 — Title
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = newSlide();
    card(slide, 0.7, 0.72, 8.6, 3.9, { transparency: 28 });
    slide.addText("DTIS · FEATURE UPDATES", { x: 1.0, y: 1.05, w: 8.0, h: 0.4, fontSize: 14, bold: true, color: C.teal, fontFace: FONT, align: "center", charSpacing: 1 });
    slide.addText("What's New in DTIS", { x: 1.0, y: 1.5, w: 8.0, h: 1.2, fontSize: 40, bold: true, color: C.white, fontFace: FONT, align: "center" });
    slide.addText("QR Code Receive  ·  Routing Logbook  ·  New & Updated Reports", { x: 1.0, y: 2.85, w: 8.0, h: 0.4, fontSize: 15, color: C.text, fontFace: FONT, align: "center" });
    slide.addShape(pptx.ShapeType.line, { x: 3.5, y: 3.4, w: 3.0, h: 0, line: { color: C.teal, width: 1, transparency: 30 } });
    slide.addText("DOH Western Visayas · Center for Health Development", { x: 1.0, y: 3.55, w: 8.0, h: 0.35, fontSize: 12, italic: true, color: C.dim, fontFace: FONT, align: "center" });
    slide.addText("[ Date ]   |   Presenter: [ Name ]", { x: 1.0, y: 3.95, w: 8.0, h: 0.35, fontSize: 11, color: C.dim, fontFace: FONT, align: "center" });
    slide.addNotes("Open with the theme: these updates take the two things that slow us down — receiving at the counter, and answering 'how are we doing?' — and make both a scan or a click away.");
}

// ════════════════════════════════════════════════════════════════════════════
// 2 — Overview
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = newSlide();
    titleTop(slide, "What We're Rolling Out", { sub: "Four updates in this release", size: 28 });
    const items = [
        ["QR Code Receive", "Scan the tracking form's QR code to receive a document — confirm once, done.", "NEW"],
        ["Routing Logbook", "The paper logbook, online — what came in, when, and who received it.", "NEW"],
        ["External Requests report", "Now tracks each request against its deadline, with status colors.", "UPDATED"],
        ["Per Unit & Turnaround Time reports", "Workload by category, and how long each office holds documents.", "NEW"],
    ];
    const y0 = 1.4, ch = 0.8, gap = 0.12;
    items.forEach(([t, d, tg], i) => {
        const y = y0 + i * (ch + gap);
        card(slide, 0.5, y, 9.0, ch);
        slide.addShape(pptx.ShapeType.roundRect, { x: 0.5, y, w: 0.1, h: ch, rectRadius: 0.03, fill: { color: tg === "NEW" ? C.teal : C.gold }, line: { type: "none" } });
        slide.addText(t, { x: 0.8, y: y + 0.1, w: 6.6, h: 0.34, fontSize: 15, bold: true, color: C.white, fontFace: FONT });
        slide.addText(d, { x: 0.8, y: y + 0.44, w: 6.9, h: 0.32, fontSize: 10.5, color: C.dim, fontFace: FONT });
        tag(slide, tg, 9.3, y + 0.2);
    });
    pill(slide, FOOT);
    slide.addNotes("Two brand-new capabilities (QR receive, logbook), one report upgraded (external requests), and two new reports.");
}

// ════════════════════════════════════════════════════════════════════════════
// 3 — QR Receive: what it is (+ printed form image)
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = newSlide();
    titleTop(slide, "QR Code Receive", { sub: "Receive a document by scanning its tracking form", size: 28, tag: "NEW", tw: 6.5 });
    // left explanation card
    card(slide, 0.5, 1.35, 5.3, 3.05);
    slide.addText([
        { text: "Every printed tracking form now carries a QR code marked ", options: { fontSize: 12, color: C.text, fontFace: FONT } },
        { text: "“SCAN TO RECEIVE.”", options: { fontSize: 12, bold: true, color: C.teal, fontFace: FONT } },
        { text: "\n\nThe receiving office scans it — no searching a list, no manual status change. One confirmation tap logs it as received and sets it to ", options: { fontSize: 12, color: C.text, fontFace: FONT } },
        { text: "On Process.", options: { fontSize: 12, bold: true, color: C.teal, fontFace: FONT } },
        { text: "\n\nThe paper form is unchanged for records — same layout and control number, plus the QR code.", options: { fontSize: 12, color: C.text, fontFace: FONT } },
    ], { x: 0.72, y: 1.5, w: 4.9, h: 2.4, valign: "top" });
    slide.addText([
        { text: "Bundle-aware — ", options: { fontSize: 11, bold: true, color: C.teal, fontFace: FONT } },
        { text: "one scan receives every attached document.", options: { fontSize: 11, color: C.text, fontFace: FONT } },
    ], { x: 0.72, y: 3.95, w: 4.9, h: 0.35, valign: "middle" });
    // right: form image
    const fw = 2.72, fh = fw * (900 / 680), fx = 6.55, fy = 1.15;
    slide.addImage({ path: "docs/screens/07-tracking-form.png", x: fx, y: fy, w: fw, h: fh });
    slide.addText("Printed tracking form (sample)", { x: fx - 0.3, y: fy + fh - 0.02, w: fw + 0.6, h: 0.28, fontSize: 9.5, italic: true, color: C.dim, fontFace: FONT, align: "center" });
    slide.addNotes("Point at the QR code in the corner of the form. Fewer steps at the counter — the QR takes staff straight to the document. Sample data on the form.");
}

// ════════════════════════════════════════════════════════════════════════════
// 4 — How it works
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = newSlide();
    titleTop(slide, "How It Works at the Counter", { sub: "From printed form to logged receipt", size: 26 });
    const steps = [
        "Print & attach the form — the QR code travels with the physical document.",
        "The receiving office scans the QR code — DTIS opens the document instantly.",
        "Review, then press Receive — a confirmation prompt prevents accidental taps.",
        "Status moves to On Process, assigned to your office.",
        "The receipt is logged automatically with office, person, and timestamp.",
    ];
    const y0 = 1.35, gap = 0.66;
    steps.forEach((t, i) => step(slide, i + 1, t, 0.6, y0 + i * gap, 8.9, 12.5));
    pill(slide, FOOT);
    slide.addNotes("Emphasize the confirmation prompt (step 3) and automatic logging (step 5) — same accountability as the paper logbook.");
}

// ════════════════════════════════════════════════════════════════════════════
// 4b — How to scan the QR (methods + phone screenshot)
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = newSlide();
    titleTop(slide, "How to Scan the QR Code", { size: 26 });
    // phone screenshot on the left
    const iw = 2.18, ih = iw * (860 / 440), ix = 0.62, iy = 1.0;
    slide.addImage({ path: "docs/screens/09-scan-qr.png", x: ix, y: iy, w: iw, h: ih });
    slide.addText("Phone camera scanning (sample)", { x: ix - 0.35, y: iy + ih - 0.02, w: iw + 0.7, h: 0.24, fontSize: 9, italic: true, color: C.dim, fontFace: FONT, align: "center" });
    // methods card on the right
    const rx = 3.35, rw = 6.15, ry = 1.15, rh = 3.32;
    card(slide, rx, ry, rw, rh);
    slide.addText([
        { text: "Three ways to scan", options: { fontSize: 14, bold: true, color: C.teal, fontFace: FONT } },
        { text: "   — most phones need no app", options: { fontSize: 11, italic: true, color: C.dim, fontFace: FONT } },
    ], { x: rx + 0.28, y: ry + 0.15, w: rw - 0.5, h: 0.34 });
    slide.addShape(pptx.ShapeType.line, { x: rx + 0.28, y: ry + 0.55, w: rw - 0.56, h: 0, line: { color: C.teal, width: 0.75, transparency: 40 } });
    const methods = [
        ["Phone Camera", "Open the Camera app, point at the QR, and tap the link that appears.  (iPhone & most Android)"],
        ["Google Lens", "Open the Google app, tap the Lens icon, point at the QR, then tap the result."],
        ["QR Scanner app", "No link appears? Install any free “QR Code Scanner” from the App Store or Play Store."],
    ];
    const my0 = ry + 0.72, mgap = 0.82;
    methods.forEach(([t, d], i) => {
        const y = my0 + i * mgap;
        slide.addShape(pptx.ShapeType.ellipse, { x: rx + 0.3, y: y + 0.02, w: 0.38, h: 0.38, fill: { color: C.teal }, line: { type: "none" } });
        slide.addText(String(i + 1), { x: rx + 0.3, y: y + 0.02, w: 0.38, h: 0.38, fontSize: 13, bold: true, color: "0B1A16", fontFace: FONT, align: "center", valign: "middle" });
        slide.addText(t, { x: rx + 0.85, y: y - 0.02, w: rw - 1.1, h: 0.3, fontSize: 12.5, bold: true, color: C.white, fontFace: FONT });
        slide.addText(d, { x: rx + 0.85, y: y + 0.26, w: rw - 1.1, h: 0.5, fontSize: 10.5, color: C.dim, fontFace: FONT, valign: "top" });
    });
    slide.addText([
        { text: "Any option opens DTIS in your browser — ", options: { fontSize: 11.5, color: C.text, fontFace: FONT } },
        { text: "log in if prompted, then tap Receive.", options: { fontSize: 11.5, bold: true, color: C.teal, fontFace: FONT } },
    ], { x: rx + 0.05, y: ry + rh + 0.12, w: rw, h: 0.35, align: "center", valign: "middle" });
    slide.addNotes("Reassure non-technical staff: no app to install on most phones — the camera does it. Google Lens is the fallback for Android phones that don't auto-detect; a QR scanner app is the last resort. Tapping the link opens DTIS; they log in once, then Receive.");
}

// ════════════════════════════════════════════════════════════════════════════
// 5 — Same code, right response (table)
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = newSlide();
    titleTop(slide, "The Same Code, the Right Response", { sub: "DTIS checks who is scanning and the document's state", size: 24 });
    const rows = [
        ["The correct office, document waiting", "Full document + Receive → Received!", "Received", C.green],
        ["A different office", "“Not Assigned to Your Office”", "Blocked", C.amber],
        ["Already received", "“Already Received”", "No change", C.blue],
        ["A completed document", "“Document Closed”", "No change", C.slate],
        ["An invalid / unknown code", "“Document Not Found”", "Rejected", C.red],
    ];
    const head = ["When it is scanned by…", "What the scanner sees", "Result"].map((h) => ({
        text: h, options: { bold: true, color: "0B1A16", fill: { color: C.teal }, fontSize: 12, fontFace: FONT, align: "center", valign: "middle" },
    }));
    const body = rows.map((r) => [
        { text: r[0], options: { fontSize: 11, color: C.text, fontFace: FONT, fill: { color: C.cardHi, transparency: 15 }, valign: "middle", margin: 4 } },
        { text: r[1], options: { fontSize: 11, color: C.text, fontFace: FONT, fill: { color: C.cardHi, transparency: 15 }, valign: "middle", margin: 4 } },
        { text: r[2], options: { fontSize: 11.5, bold: true, color: C.white, fontFace: FONT, fill: { color: r[3] }, align: "center", valign: "middle" } },
    ]);
    slide.addTable([head, ...body], {
        x: 0.5, y: 1.4, w: 9.0, colW: [3.3, 3.9, 1.8], rowH: [0.4, 0.56, 0.56, 0.56, 0.56, 0.56],
        border: { type: "solid", color: "3A5560", pt: 0.5 }, valign: "middle",
    });
    slide.addText("A QR code is a guardrail, not a blind button — the wrong office can't receive, and nothing gets received twice.", {
        x: 0.5, y: 5.02, w: 9.0, h: 0.35, fontSize: 11, italic: true, color: C.dim, fontFace: FONT, align: "center",
    });
    slide.addNotes("Read one row aloud. Reassure that it's safe by design. The next two slides show the actual screens.");
}

// ════════════════════════════════════════════════════════════════════════════
// 6 — Screenshots: happy path
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = newSlide();
    titleTop(slide, "A Successful Scan", { sub: "What the receiving office sees — sample data", size: 26 });
    const w = 4.35, y = 1.55;
    shot(slide, "01-confirm.png", 0.5, y, w, "1 · Scan opens the document", C.teal);
    shot(slide, "02-success.png", 5.15, y, w, "2 · Confirm → Received", C.teal);
    slide.addText("→", { x: 4.72, y: y + 1.35, w: 0.45, h: 0.5, fontSize: 26, bold: true, color: C.teal, fontFace: FONT, align: "center" });
    slide.addText("Status becomes On Process and the receipt is logged with the office, person, and time.", {
        x: 0.5, y: 5.05, w: 9.0, h: 0.32, fontSize: 11, italic: true, color: C.dim, fontFace: FONT, align: "center",
    });
    slide.addNotes("Illustrative screens with sample data (control no. 2026-07-000482), not live records. Left to right: scan shows the document, one confirm receives it.");
}

// ════════════════════════════════════════════════════════════════════════════
// 7 — Screenshots: guardrails
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = newSlide();
    titleTop(slide, "Guardrails — When DTIS Says “Not Yet”", { sub: "The same code, a different response for each situation", size: 22 });
    const cells = [
        ["03-wrong-office.png", "Wrong office"],
        ["04-already-received.png", "Already received"],
        ["05-closed.png", "Document closed"],
        ["06-not-found.png", "Invalid QR code"],
    ];
    const w = 2.16, gap = 0.16, y = 1.9;
    const startX = (W - (cells.length * w + (cells.length - 1) * gap)) / 2;
    cells.forEach(([file, cap], i) => shot(slide, file, startX + i * (w + gap), y, w, cap, C.teal));
    slide.addText("The wrong office can't receive a document, and nothing gets received twice — the scanner is always told exactly why.", {
        x: 0.5, y: 4.35, w: 9.0, h: 0.4, fontSize: 11.5, italic: true, color: C.dim, fontFace: FONT, align: "center",
    });
    pill(slide, FOOT);
    slide.addNotes("Four protective outcomes from the same QR code. Reassure staff they can't 'break' anything by scanning.");
}

// ════════════════════════════════════════════════════════════════════════════
// 8 — Routing Logbook
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = newSlide();
    titleTop(slide, "Routing Logbook", { sub: "The paper logbook, online — per office", size: 28, tag: "NEW", tw: 6.5 });
    twoPanel(slide,
        { title: "What it generates", lines: [
            "A dated list of documents routed to your office.",
            "For each: control number, category, date routed.",
            "Who received it, and the exact time received.",
            "Newest first; choose any date range.",
        ] },
        { title: "Why it matters", lines: [
            "Replaces the handwritten routing logbook.",
            "Same accountability, captured automatically.",
            "Ready for audits and reconciliation anytime.",
            "One trusted answer to “did we receive it, and when?”",
        ] },
        1.35, 2.95
    );
    slide.addText([
        { text: "In the demo:  ", options: { fontSize: 10.5, bold: true, color: C.gold, fontFace: FONT } },
        { text: "the view defaults to yesterday–today — widen the date filter for older records.", options: { fontSize: 10.5, color: C.text, fontFace: FONT } },
    ], { x: 0.5, y: 4.5, w: 9.0, h: 0.35, align: "center", valign: "middle" });
    slide.addNotes("Frame it as a familiar thing made easier: their logbook, without the paper and handwriting.");
}

// ════════════════════════════════════════════════════════════════════════════
// 8b — Routing Logbook screenshot (portrait, sample data)
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = newSlide();
    titleTop(slide, "Routing Logbook — In the App", { sub: "Live receipt tracking, at a glance — sample data", size: 24, tag: "NEW", tw: 6.3 });
    // portrait screenshot on the left
    const iw = 2.72, ih = iw * (864 / 600), ix = 0.6, iy = 1.42;
    slide.addImage({ path: "docs/screens/08-routing-logbook.png", x: ix, y: iy, w: iw, h: ih });
    slide.addText("Routing Logbook (sample data)", { x: ix - 0.3, y: iy + ih - 0.01, w: iw + 0.6, h: 0.24, fontSize: 9, italic: true, color: C.dim, fontFace: FONT, align: "center" });
    // highlights card on the right
    const rx = 3.95, rw = 5.55, ry = 1.42, rh = 3.55;
    card(slide, rx, ry, rw, rh);
    slide.addText("What you see", { x: rx + 0.25, y: ry + 0.16, w: rw - 0.5, h: 0.34, fontSize: 14, bold: true, color: C.teal, fontFace: FONT });
    slide.addShape(pptx.ShapeType.line, { x: rx + 0.25, y: ry + 0.56, w: rw - 0.5, h: 0, line: { color: C.teal, width: 0.75, transparency: 40 } });
    const points = [
        [["A ", false], ["Live", true], [" list of every document your office has forwarded.", false]],
        [["Control number, subject, and ", false], ["destination office.", true]],
        [["Who received it and ", false], ["exactly when", true], [" — the receiver's name and timestamp.", false]],
        [["A per-row ", false], ["receipt status", true], [": Received, Awaiting, or Returned.", false]],
        [["A running tally — ", false], ["“4 / 7 received”", true], [" — updates as receipts come in.", false]],
    ];
    const items = points.map((parts) => parts.map(([t, em]) => ({
        text: t, options: { fontSize: 11.5, fontFace: FONT, color: em ? C.teal : C.text, bold: !!em, breakLine: false },
    })));
    // flatten with bullets per line
    const body = [];
    items.forEach((line, i) => {
        line.forEach((run, j) => {
            const o = { ...run.options, bullet: j === 0 ? { code: "2013", indent: 14 } : false, paraSpaceAfter: 0 };
            if (j === line.length - 1) o.breakLine = true;
            body.push({ text: run.text, options: o });
        });
        if (i < items.length - 1) body.push({ text: "", options: { fontSize: 6, breakLine: true } });
    });
    slide.addText(body, { x: rx + 0.28, y: ry + 0.68, w: rw - 0.55, h: rh - 0.85, valign: "top" });
    pill(slide, FOOT);
    slide.addNotes("Portrait screenshot with sample data (not live records). Walk one row: forwarded to ACCT, received by Dela Cruz, Maria at 09:41 — that receipt row turns green. Awaiting rows are still out; Returned came back.");
}

// ════════════════════════════════════════════════════════════════════════════
// 9 — Reports divider
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = newSlide();
    card(slide, 0.9, 1.6, 8.2, 2.4, { transparency: 30 });
    slide.addText("REPORTS", { x: 0.9, y: 1.9, w: 8.2, h: 0.9, fontSize: 40, bold: true, color: C.white, fontFace: FONT, align: "center", charSpacing: 2 });
    slide.addText("Three reports — each answers one plain question", { x: 0.9, y: 2.85, w: 8.2, h: 0.4, fontSize: 14, color: C.teal, fontFace: FONT, align: "center" });
    slide.addText("“Are we meeting deadlines?”   ·   “How much of what did we handle?”   ·   “Where does the time go?”", {
        x: 0.9, y: 3.35, w: 8.2, h: 0.4, fontSize: 11, italic: true, color: C.dim, fontFace: FONT, align: "center",
    });
    slide.addNotes("These updates improve output too — knowing how we're doing. Each report is simple and answers one question.");
}

// ════════════════════════════════════════════════════════════════════════════
// 10 — External Requests (updated)
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = newSlide();
    titleTop(slide, "External Requests Report", { sub: "“Are we meeting our deadlines?”", size: 26, tag: "UPDATED", tw: 6.5 });
    twoPanel(slide,
        { title: "What it generates", lines: [
            "Every external request for your office in a date range.",
            "Each tracked against its deadline (working days by category).",
            "Color-coded: Completed, Due soon, Overdue, Pending.",
            "Where it started, where it sits now, latest remark.",
            "Printable for filing and records.",
        ] },
        { title: "Why it matters", lines: [
            "Turns a plain list into a deadline monitor.",
            "Supports Citizen Charter / Anti-Red Tape Act timeliness.",
            "Overdue items surface before they become complaints.",
            "Note: weekdays only — holidays not deducted.",
        ] },
        1.35, 2.85
    );
    // legend chips
    const leg = [["Completed", C.green], ["Due soon", C.amber], ["Overdue", C.red], ["Pending", C.slate]];
    let lx = 1.4;
    leg.forEach(([t, col]) => {
        slide.addShape(pptx.ShapeType.ellipse, { x: lx, y: 4.5, w: 0.14, h: 0.14, fill: { color: col }, line: { type: "none" } });
        slide.addText(t, { x: lx + 0.2, y: 4.4, w: 1.3, h: 0.34, fontSize: 10.5, color: C.text, fontFace: FONT, valign: "middle" });
        lx += 1.7;
    });
    slide.addNotes("Lead with the change: it now measures against deadlines, not just lists documents. Be honest about the holiday caveat.");
}

// ════════════════════════════════════════════════════════════════════════════
// 11 — Per Unit (new)
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = newSlide();
    titleTop(slide, "Per Unit Report", { sub: "“How much of what did we handle?”", size: 26, tag: "NEW", tw: 6.5 });
    twoPanel(slide,
        { title: "What it generates", lines: [
            "Document counts broken down by category.",
            "Grouped into Purchase Requests, Payments, General.",
            "Per-group subtotals and a grand total.",
            "Filterable by office, source, status, date range.",
        ] },
        { title: "Why it matters", lines: [
            "Shows the shape of a unit's workload.",
            "Helps with staffing, planning, workload balancing.",
            "A clean volume snapshot for period reporting.",
            "Pair with Turnaround Time for speed.",
        ] },
        1.35, 2.6
    );
    const stats = [["128", "Purchase Req."], ["94", "Payments"], ["57", "General"], ["279", "Total"]];
    let sx = 0.5; const sw = 2.16, sgap = 0.13;
    stats.forEach(([v, l]) => {
        card(slide, sx, 4.2, sw, 0.85, { transparency: 14 });
        slide.addText(v, { x: sx, y: 4.27, w: sw, h: 0.45, fontSize: 22, bold: true, color: C.teal, fontFace: FONT, align: "center" });
        slide.addText(l, { x: sx, y: 4.72, w: sw, h: 0.28, fontSize: 9.5, color: C.dim, fontFace: FONT, align: "center" });
        sx += sw + sgap;
    });
    slide.addNotes("Example numbers are illustrative — swap in a real period if you can. A counting report, not a timing one.");
}

// ════════════════════════════════════════════════════════════════════════════
// 12 — Turnaround Time (new)
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = newSlide();
    titleTop(slide, "Turnaround Time Report", { sub: "“Where does the time go?”", size: 26, tag: "NEW", tw: 6.5 });
    twoPanel(slide,
        { title: "What it generates", lines: [
            "How long each office holds a document (receive → forward/close).",
            "Average, fastest, slowest dwell per office, in working days.",
            "Expand an office for a breakdown by category.",
            "A live count of documents sitting at each office now.",
        ] },
        { title: "Why it matters", lines: [
            "Pinpoints where documents wait — the real bottlenecks.",
            "Turns “it feels slow” into a measured number.",
            "Supports performance review and process improvement.",
            "Read fairly: weekends excluded; origin office not counted.",
        ] },
        1.35, 2.6
    );
    const stats = [["2.4 d", "Avg dwell"], ["0 d", "Fastest"], ["11 d", "Slowest"], ["6", "Sitting now"]];
    let sx = 0.5; const sw = 2.16, sgap = 0.13;
    stats.forEach(([v, l]) => {
        card(slide, sx, 4.2, sw, 0.85, { transparency: 14 });
        slide.addText(v, { x: sx, y: 4.27, w: sw, h: 0.45, fontSize: 22, bold: true, color: C.teal, fontFace: FONT, align: "center" });
        slide.addText(l, { x: sx, y: 4.72, w: sw, h: 0.28, fontSize: 9.5, color: C.dim, fontFace: FONT, align: "center" });
        sx += sw + sgap;
    });
    slide.addNotes("The most analytical report. Frame for supervisors: this is how you find the bottleneck. Walk the 'read fairly' line.");
}

// ════════════════════════════════════════════════════════════════════════════
// 13 — Summary
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = newSlide();
    titleTop(slide, "What Changes for the People Using It", { size: 24 });
    const cards = [
        ["Fewer steps to receive", "Scan, confirm, done — with checks that stop wrong-office and double receipts."],
        ["The logbook, without paper", "Every routed document and its receiver, searchable by date."],
        ["Numbers offices can act on", "Deadlines, workload by type, and where time is spent."],
    ];
    let cx = 0.5; const cw = 2.95, cgap = 0.13;
    cards.forEach(([t, d]) => {
        card(slide, cx, 1.35, cw, 2.0);
        slide.addShape(pptx.ShapeType.roundRect, { x: cx, y: 1.35, w: cw, h: 0.08, rectRadius: 0.02, fill: { color: C.teal }, line: { type: "none" } });
        slide.addText(t, { x: cx + 0.2, y: 1.6, w: cw - 0.4, h: 0.7, fontSize: 14, bold: true, color: C.white, fontFace: FONT, valign: "top" });
        slide.addText(d, { x: cx + 0.2, y: 2.3, w: cw - 0.4, h: 0.95, fontSize: 11, color: C.text, fontFace: FONT, valign: "top" });
        cx += cw + cgap;
    });
    card(slide, 0.5, 3.6, 9.0, 1.15, { fill: C.cardHi, transparency: 10 });
    slide.addText("One line to open with", { x: 0.75, y: 3.72, w: 8.5, h: 0.3, fontSize: 11.5, bold: true, color: C.teal, fontFace: FONT });
    slide.addText("“These updates take the two things that slow us down — receiving at the counter, and answering ‘how are we doing?’ — and make both a scan or a click away.”", {
        x: 0.75, y: 4.02, w: 8.5, h: 0.7, fontSize: 12, italic: true, color: C.white, fontFace: FONT, valign: "top",
    });
    slide.addText("Thank you!", { x: 0.5, y: 4.85, w: 9.0, h: 0.4, fontSize: 16, bold: true, color: C.teal, fontFace: FONT, align: "center" });
    slide.addNotes("Close on value to the user, not the feature list. Offer to swap sample report numbers for real ones.");
}

// ── Save ──────────────────────────────────────────────────────────────────────
const OUT_FILE = process.env.PPTX_OUT || "docs/DTIS-Updates.pptx";
pptx.writeFile({ fileName: OUT_FILE })
    .then(() => console.log("✅  Saved: " + OUT_FILE))
    .catch((e) => { console.error("❌  Error:", e); process.exit(1); });

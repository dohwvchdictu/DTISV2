const PptxGenJS = require("pptxgenjs");

const pptx = new PptxGenJS();
pptx.layout = "LAYOUT_WIDE";
pptx.author = "DTIS";
pptx.title = "DTIS — Feature Updates";

// ── Theme ────────────────────────────────────────────────────────────────────
// Consistent with docs/generate-pptx.cjs, with DTIS emerald as the feature accent.
const C = {
    navy:    "1B3A5C",
    blue:    "2563EB",
    accent:  "3B82F6",
    light:   "EFF6FF",
    white:   "FFFFFF",
    gray:    "6B7280",
    dark:    "1F2937",
    green:   "059669", // DTIS emerald
    greenDk: "047857",
    greenLt: "ECFDF5",
    amber:   "D97706",
    amberLt: "FEF3C7",
    red:     "DC2626",
    redLt:   "FEE2E2",
    slate:   "64748B",
    slateLt: "F1F5F9",
};

const FONT = "Calibri";

// ── Helpers ──────────────────────────────────────────────────────────────────
function addSlide(opts = {}) {
    const slide = pptx.addSlide();
    slide.background = { color: opts.bg || C.white };
    return slide;
}

function headerBar(slide, title, subtitle, tag) {
    slide.addShape(pptx.ShapeType.rect, { x: 0, y: 0, w: "100%", h: 1.4, fill: { color: C.navy } });
    slide.addText(title, {
        x: 0.4, y: 0.12, w: 10.2, h: 0.8,
        fontSize: 24, bold: true, color: C.white, fontFace: FONT,
    });
    if (subtitle) {
        slide.addText(subtitle, {
            x: 0.4, y: 0.88, w: 10.2, h: 0.45,
            fontSize: 13, color: C.accent, fontFace: FONT, italic: true,
        });
    }
    if (tag) {
        const tg = tag === "NEW"
            ? { fill: C.green, text: "NEW" }
            : { fill: C.amber, text: "UPDATED" };
        slide.addShape(pptx.ShapeType.roundRect, {
            x: 11.55, y: 0.42, w: 1.35, h: 0.5, rectRadius: 0.08,
            fill: { color: tg.fill },
        });
        slide.addText(tg.text, {
            x: 11.55, y: 0.42, w: 1.35, h: 0.5,
            fontSize: 13, bold: true, color: C.white, fontFace: FONT, align: "center", valign: "middle",
        });
    }
    slide.addShape(pptx.ShapeType.rect, { x: 0, y: 1.4, w: "100%", h: 0.06, fill: { color: C.green } });
}

function footerBar(slide, text = "DTIS — Feature Updates") {
    slide.addShape(pptx.ShapeType.rect, { x: 0, y: 6.9, w: "100%", h: 0.38, fill: { color: C.navy } });
    slide.addText(text, { x: 0.3, y: 6.95, w: 9.5, h: 0.28, fontSize: 9, color: C.white, fontFace: FONT });
}

function bodyText(slide, lines, opts = {}) {
    const rows = lines.map((line) =>
        typeof line === "string"
            ? { text: line, options: { fontSize: opts.fontSize || 16, color: opts.color || C.dark, fontFace: FONT, paraSpaceAfter: opts.gap || 8 } }
            : line
    );
    slide.addText(rows, {
        x: opts.x || 0.4, y: opts.y || 1.6, w: opts.w || 12.2, h: opts.h || 4.6,
        bullet: opts.bullet !== false ? { type: "bullet", characterCode: "25CF", color: C.green } : false,
        valign: "top", ...opts.extra,
    });
}

// numbered step list
function stepList(slide, steps, opts = {}) {
    const x = opts.x || 0.4, w = opts.w || 12.2;
    const startY = opts.y || 1.7, gap = opts.gap || 0.72, color = opts.color || C.green;
    steps.forEach(([num, text], i) => {
        const y = startY + i * gap;
        slide.addShape(pptx.ShapeType.ellipse, { x, y: y + 0.02, w: 0.44, h: 0.44, fill: { color } });
        slide.addText(String(num), {
            x, y: y + 0.02, w: 0.44, h: 0.44,
            fontSize: 14, bold: true, color: C.white, fontFace: FONT, align: "center", valign: "middle",
        });
        slide.addText(text, {
            x: x + 0.6, y, w: w - 0.6, h: gap,
            fontSize: opts.fontSize || 16, color: C.dark, fontFace: FONT, valign: "middle",
        });
    });
}

// two-panel "What it generates" / "Why it matters"
function twoPanel(slide, left, right, y = 1.7, h = 3.7) {
    const panels = [left, right];
    panels.forEach((p, i) => {
        const x = i === 0 ? 0.4 : 6.7;
        slide.addShape(pptx.ShapeType.rect, {
            x, y, w: 5.9, h, fill: { color: i === 0 ? C.greenLt : C.light }, line: { color: i === 0 ? C.green : C.accent, pt: 1.5 },
        });
        slide.addText(p.title, {
            x, y, w: 5.9, h: 0.55, fontSize: 15, bold: true, color: C.white, fontFace: FONT,
            align: "center", valign: "middle", fill: { color: i === 0 ? C.green : C.blue },
        });
        slide.addText(
            p.lines.map((t) => ({ text: t, options: { fontSize: 13.5, color: C.dark, fontFace: FONT, paraSpaceAfter: 7, bullet: { type: "bullet", characterCode: "2013", indent: 14 } } })),
            { x: x + 0.25, y: y + 0.75, w: 5.4, h: h - 0.95, valign: "top" }
        );
    });
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 1 — Title
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide({ bg: C.navy });
    slide.addShape(pptx.ShapeType.rect, { x: 0, y: 0, w: "100%", h: "100%", fill: { color: C.navy } });
    slide.addShape(pptx.ShapeType.rect, { x: 0, y: 4.7, w: "100%", h: 2.6, fill: { color: C.green } });

    slide.addText("DTIS", { x: 0.8, y: 0.7, w: 11.4, h: 0.7, fontSize: 18, color: C.green, fontFace: FONT, align: "center", bold: true });
    slide.addText("What's New in DTIS", { x: 0.8, y: 1.35, w: 11.4, h: 1.6, fontSize: 44, bold: true, color: C.white, fontFace: FONT, align: "center" });
    slide.addText("Feature Updates — QR Receive, Routing Logbook & New Reports", {
        x: 0.8, y: 3.15, w: 11.4, h: 0.6, fontSize: 18, color: C.light, fontFace: FONT, align: "center",
    });
    slide.addText("Document Tracking Information System", { x: 0.8, y: 5.05, w: 11.4, h: 0.6, fontSize: 22, bold: true, color: C.white, fontFace: FONT, align: "center" });
    slide.addText("DOH Western Visayas  ·  Center for Health Development  |  [Date]  |  Presenter: [Name]", {
        x: 0.8, y: 5.75, w: 11.4, h: 0.45, fontSize: 13, color: C.white, fontFace: FONT, align: "center",
    });
    slide.addNotes("Open with the theme: these updates take the two things that slow us down — receiving at the counter, and answering 'how are we doing?' — and make both a scan or a click away.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 2 — Overview of the updates
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "What We're Rolling Out", "Four updates in this release");
    footerBar(slide);

    const items = [
        ["QR Code Receive", "Scan the tracking form's QR code to receive a document — confirm once, done.", C.green, "NEW"],
        ["Routing Logbook", "The paper logbook, online — what came in, when, and who received it.", C.green, "NEW"],
        ["External Requests report", "Now tracks each request against its deadline, with status colors.", C.amber, "UPDATED"],
        ["Per Unit & Turnaround Time reports", "New reports: workload by category, and how long each office holds documents.", C.green, "NEW"],
    ];
    items.forEach(([t, d, col, tag], i) => {
        const y = 1.7 + i * 1.24;
        slide.addShape(pptx.ShapeType.rect, { x: 0.4, y, w: 12.2, h: 1.05, fill: { color: C.slateLt }, line: { color: "E5E7EB", pt: 1 } });
        slide.addShape(pptx.ShapeType.rect, { x: 0.4, y, w: 0.12, h: 1.05, fill: { color: col } });
        slide.addText(t, { x: 0.75, y: y + 0.12, w: 8.7, h: 0.45, fontSize: 17, bold: true, color: C.navy, fontFace: FONT });
        slide.addText(d, { x: 0.75, y: y + 0.55, w: 9.3, h: 0.42, fontSize: 13, color: C.gray, fontFace: FONT });
        slide.addShape(pptx.ShapeType.roundRect, { x: 10.9, y: y + 0.32, w: 1.4, h: 0.42, rectRadius: 0.07, fill: { color: tag === "NEW" ? C.green : C.amber } });
        slide.addText(tag, { x: 10.9, y: y + 0.32, w: 1.4, h: 0.42, fontSize: 12, bold: true, color: C.white, fontFace: FONT, align: "center", valign: "middle" });
    });
    slide.addNotes("Set expectations: two brand-new capabilities (QR receive, logbook), one report upgraded (external requests), and two new reports. Then walk each one.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 3 — QR Receive: what it is
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "QR Code Receive", "Receive a document by scanning its tracking form", "NEW");
    footerBar(slide);
    // Left column: explanation
    bodyText(slide, [
        "Every printed Document Tracking Form now carries a QR code in the top-right corner, marked “SCAN TO RECEIVE.”",
        "The receiving office scans it with a phone or scanner — no searching a list, no manual status change.",
        "One confirmation tap, and the document is logged as received and set to On Process.",
        "The paper form is unchanged for records — same layout and control number, plus the QR code.",
    ], { x: 0.4, y: 1.7, w: 7.9, h: 3.1, fontSize: 16, gap: 11 });

    slide.addShape(pptx.ShapeType.rect, { x: 0.4, y: 5.0, w: 7.9, h: 1.25, fill: { color: C.greenLt }, line: { color: C.green, pt: 1 } });
    slide.addText([
        { text: "Bundle-aware:  ", options: { fontSize: 14, bold: true, color: C.greenDk, fontFace: FONT } },
        { text: "scanning a bundle receives every attached document in one step.", options: { fontSize: 14, color: C.dark, fontFace: FONT } },
    ], { x: 0.65, y: 5.1, w: 7.4, h: 1.05, valign: "middle" });

    // Right column: the printed form (sample data)
    const fw = 3.62, fh = fw * (900 / 680), fx = 8.85, fy = 1.62;
    slide.addImage({ path: "docs/screens/07-tracking-form.png", x: fx, y: fy, w: fw, h: fh });
    slide.addText("Printed tracking form (sample) — QR at top-right", {
        x: fx - 0.3, y: fy + fh - 0.02, w: fw + 0.6, h: 0.3, fontSize: 10, italic: true, color: C.gray, fontFace: FONT, align: "center",
    });
    slide.addNotes("Point at the QR code in the corner of the form. The key selling point: fewer steps at the counter — staff no longer hunt for the document in the Incoming list, the QR takes them straight to it. Sample data on the form.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 4 — QR Receive: how it works
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "How It Works at the Counter", "From printed form to logged receipt");
    footerBar(slide);
    stepList(slide, [
        [1, "Print & attach the form — the QR code is already on it and travels with the physical document."],
        [2, "The receiving office scans the QR code — DTIS opens the document instantly."],
        [3, "Review the document, then press Receive — a confirmation prompt prevents accidental taps."],
        [4, "Status moves to On Process, assigned to your office."],
        [5, "The receipt is logged automatically with the office, person, and timestamp."],
    ], { y: 1.75, gap: 0.82, fontSize: 17 });
    slide.addNotes("Emphasize step 3: the confirmation prompt. And step 5: it is logged with a name and time — same accountability as the paper logbook, automatically.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 5 — QR Receive: same code, right response (the guardrail)
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "The Same Code, the Right Response", "DTIS checks who is scanning and the document's state");
    footerBar(slide);

    const head = ["When it is scanned by…", "What the scanner sees", "Result"];
    const rows = [
        [["The correct office, document waiting", C.white], ["Full document + Receive button → Received!", C.greenLt], ["Received ✓", C.green]],
        [["A different office", C.white],                    ["“Not Assigned to Your Office”", C.amberLt],     ["Blocked", C.amber]],
        [["Anyone, after it's already received", C.white],   ["“Already Received”", C.light],                  ["No change", C.blue]],
        [["Anyone, on a completed document", C.white],       ["“Document Closed”", C.slateLt],                 ["No change", C.slate]],
        [["An invalid / unknown code", C.white],             ["“Document Not Found”", C.redLt],                ["Rejected", C.red]],
    ];
    const table = [
        head.map((h) => ({ text: h, options: { bold: true, color: C.white, fill: { color: C.navy }, fontSize: 13, fontFace: FONT, align: "center", valign: "middle" } })),
        ...rows.map((r) => r.map(([txt, fill], ci) => ({
            text: txt,
            options: {
                fontSize: 12.5, fontFace: FONT, fill: { color: fill }, valign: "middle",
                color: ci === 2 ? C.white : C.dark,
                bold: ci === 2,
                align: ci === 2 ? "center" : "left",
            },
        }))),
    ];
    // recolor the Result cells to solid status colors
    rows.forEach((r, ri) => { table[ri + 1][2].options.fill = { color: r[2][1] }; });

    slide.addTable(table, {
        x: 0.4, y: 1.7, w: 12.2, colW: [4.2, 5.6, 2.4], rowH: [0.5, 0.82, 0.82, 0.82, 0.82, 0.82],
        border: { type: "solid", color: "D1D5DB", pt: 0.5 }, valign: "middle",
    });
    slide.addText("A QR code is not a blind “receive” button — it is a guardrail. The wrong office can't receive a document, and nothing gets received twice.", {
        x: 0.4, y: 6.35, w: 12.2, h: 0.45, fontSize: 13, italic: true, color: C.gray, fontFace: FONT,
    });
    slide.addNotes("This is the most important QR slide. Read one row aloud: 'If the wrong office scans it, they can't receive it — they're told it belongs elsewhere.' Reassure that it's safe by design.");
}

// Screenshot aspect: 700 x 460
const SHOT = 460 / 700;
function shot(slide, file, x, y, w, caption, capColor) {
    if (caption) {
        slide.addText(caption, {
            x, y, w, h: 0.32, fontSize: 12.5, bold: true, color: capColor || C.navy,
            fontFace: FONT, align: "center",
        });
    }
    slide.addImage({ path: "docs/screens/" + file, x, y: y + (caption ? 0.36 : 0), w, h: w * SHOT });
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 5b — Screenshots: the happy path (sample data)
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "A Successful Scan", "What the receiving office sees — sample data");
    footerBar(slide);
    const w = 5.95, y = 2.0;
    shot(slide, "01-confirm.png", 0.5, y, w, "1 · Scan opens the document", C.greenDk);
    shot(slide, "02-success.png", 6.85, y, w, "2 · Confirm → Received", C.greenDk);
    // arrow between
    slide.addText("→", { x: 6.35, y: y + 1.7, w: 0.5, h: 0.6, fontSize: 30, bold: true, color: C.green, fontFace: FONT, align: "center" });
    slide.addText("Status becomes On Process and the receipt is logged with the office, person, and time.", {
        x: 0.5, y: 6.3, w: 12.3, h: 0.45, fontSize: 13, italic: true, color: C.gray, fontFace: FONT, align: "center",
    });
    slide.addNotes("These are illustrative screens with sample data (control no. 2026-07-000482), not live records. Walk left to right: scan shows the document, one confirm receives it.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 5c — Screenshots: the guardrails (sample data)
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "Guardrails — When DTIS Says “Not Yet”", "The same code, a different response for each situation");
    footerBar(slide);
    const w = 3.02, y = 2.45;
    const cells = [
        ["03-wrong-office.png", "Wrong office", C.amber],
        ["04-already-received.png", "Already received", C.blue],
        ["05-closed.png", "Document closed", C.slate],
        ["06-not-found.png", "Invalid QR code", C.red],
    ];
    const gap = 0.14;
    const startX = (13.33 - (cells.length * w + (cells.length - 1) * gap)) / 2;
    cells.forEach(([file, cap, col], i) => {
        shot(slide, file, startX + i * (w + gap), y, w, cap, col);
    });
    slide.addText("The wrong office can't receive a document, and nothing gets received twice — the scanner is always told exactly why.", {
        x: 0.5, y: 5.7, w: 12.3, h: 0.5, fontSize: 14, italic: true, color: C.gray, fontFace: FONT, align: "center",
    });
    slide.addNotes("Four protective outcomes, all from the same QR code. Sample data. Reassure staff: they can't 'break' anything by scanning — DTIS decides what's allowed.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 6 — Routing Logbook
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "Routing Logbook", "The paper logbook, online — per office", "NEW");
    footerBar(slide);
    twoPanel(slide,
        {
            title: "What it generates",
            lines: [
                "A dated list of documents routed to your office (the “For Receiving” entries).",
                "For each: control number, category, date routed.",
                "Who received it, and the exact time received.",
                "Newest first; choose any date range.",
            ],
        },
        {
            title: "Why it matters",
            lines: [
                "Replaces the handwritten routing logbook — no more illegible entries.",
                "Same accountability the paper book gave you, captured automatically.",
                "Ready for audits and reconciliation at any time.",
                "One trusted place to answer “did we receive it, and when?”",
            ],
        },
        1.7, 3.7
    );
    slide.addShape(pptx.ShapeType.rect, { x: 0.4, y: 5.65, w: 12.2, h: 0.85, fill: { color: C.amberLt }, line: { color: C.amber, pt: 1 } });
    slide.addText([
        { text: "In the demo:  ", options: { fontSize: 13, bold: true, color: C.amber, fontFace: FONT } },
        { text: "the view defaults to yesterday–today — remind staff to widen the date filter for older records.", options: { fontSize: 13, color: C.dark, fontFace: FONT } },
    ], { x: 0.65, y: 5.72, w: 11.8, h: 0.7, valign: "middle" });
    slide.addNotes("Frame it as a familiar thing made easier: it's their logbook, without the paper and the handwriting.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 7 — Reports section divider
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide({ bg: C.navy });
    slide.addShape(pptx.ShapeType.rect, { x: 0, y: 0, w: "100%", h: "100%", fill: { color: C.navy } });
    slide.addShape(pptx.ShapeType.rect, { x: 0, y: 3.1, w: "100%", h: 0.08, fill: { color: C.green } });
    slide.addText("Reports", { x: 0.8, y: 2.0, w: 11.4, h: 1.0, fontSize: 40, bold: true, color: C.white, fontFace: FONT, align: "center" });
    slide.addText("Three reports — each answers one plain question", {
        x: 0.8, y: 3.35, w: 11.4, h: 0.6, fontSize: 18, color: C.light, fontFace: FONT, align: "center",
    });
    slide.addText("“Are we meeting deadlines?”    ·    “How much of what did we handle?”    ·    “Where does the time go?”", {
        x: 0.8, y: 4.2, w: 11.4, h: 0.5, fontSize: 14, italic: true, color: C.green, fontFace: FONT, align: "center",
    });
    slide.addNotes("Transition: these updates aren't just about input (receiving) — they also improve output (knowing how we're doing). Each report is kept simple and answers one question.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 8 — External Requests (Updated)
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "External Requests Report", "“Are we meeting our deadlines?”", "UPDATED");
    footerBar(slide);
    twoPanel(slide,
        {
            title: "What it generates",
            lines: [
                "Every external request for your office within a chosen date range.",
                "Each request tracked against its deadline (required working days by category).",
                "Color-coded status: Completed, Due soon, Overdue, Pending.",
                "Where it started, where it sits now, and the latest remark.",
                "Printable for filing and records.",
            ],
        },
        {
            title: "Why it matters",
            lines: [
                "Turns a plain list into a deadline monitor — you see what needs attention at a glance.",
                "Supports Citizen Charter / Anti-Red Tape Act timeliness.",
                "Overdue items surface before they become complaints.",
                "Note: counts weekdays only — holidays are not deducted, so treat “overdue” as a guide near holidays.",
            ],
        },
        1.7, 4.5
    );
    slide.addNotes("This is the report you UPDATED. Lead with the change: it now measures against deadlines, not just lists documents. Be honest about the holiday caveat.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 9 — Per Unit (New)
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "Per Unit Report", "“How much of what did we handle?”", "NEW");
    footerBar(slide);
    twoPanel(slide,
        {
            title: "What it generates",
            lines: [
                "Document counts broken down by category.",
                "Grouped into Purchase Requests, Payments, and General.",
                "Per-group subtotals and a grand total.",
                "Filterable by office, source, status, and date range.",
            ],
        },
        {
            title: "Why it matters",
            lines: [
                "Shows the shape of a unit's workload — what kinds of documents dominate.",
                "Helps with staffing, planning, and workload balancing.",
                "A clean volume snapshot for period reporting.",
                "Pair with Turnaround Time when the question shifts from “how much” to “how fast.”",
            ],
        },
        1.7, 3.6
    );
    // sample stat strip
    const stats = [["128", "Purchase Requests"], ["94", "Payments"], ["57", "General"], ["279", "Total"]];
    stats.forEach(([v, l], i) => {
        const x = 0.4 + i * 3.1;
        slide.addShape(pptx.ShapeType.rect, { x, y: 5.55, w: 2.9, h: 0.95, fill: { color: C.slateLt }, line: { color: "E5E7EB", pt: 1 } });
        slide.addText(v, { x, y: 5.62, w: 2.9, h: 0.55, fontSize: 26, bold: true, color: C.greenDk, fontFace: FONT, align: "center" });
        slide.addText(l, { x, y: 6.12, w: 2.9, h: 0.32, fontSize: 11, color: C.gray, fontFace: FONT, align: "center" });
    });
    slide.addNotes("New report. The example numbers are illustrative — swap in a real recent period if you can. Emphasize it's a counting report, not a timing one.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 10 — Turnaround Time (New)
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "Turnaround Time Report", "“Where does the time go?”", "NEW");
    footerBar(slide);
    twoPanel(slide,
        {
            title: "What it generates",
            lines: [
                "How long each office holds a document — from receiving it to forwarding, endorsing, returning, or closing it.",
                "Average, fastest, and slowest dwell time per office, in working days.",
                "Expand an office for a breakdown by category.",
                "A live count of documents sitting at each office right now.",
            ],
        },
        {
            title: "Why it matters",
            lines: [
                "Pinpoints where documents wait — the real bottlenecks in the flow.",
                "Turns “it feels slow” into a measured number you can act on.",
                "Supports performance review and process improvement.",
                "Read fairly: weekends excluded (not holidays); the origin office isn't counted; figures refresh every few minutes.",
            ],
        },
        1.7, 4.5
    );
    slide.addNotes("The most analytical report. Frame it for supervisors: this is how you find the bottleneck. Walk the 'read fairly' line so no one distrusts the numbers.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 11 — Summary / closing
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "What Changes for the People Using It");
    footerBar(slide);

    const cards = [
        ["Fewer steps to receive", "Scan, confirm, done — with checks that stop wrong-office and double receipts.", C.green],
        ["The logbook, without paper", "Every routed document and its receiver, searchable by date.", C.blue],
        ["Numbers offices can act on", "Deadlines, workload by type, and where time is spent — three clear answers.", C.navy],
    ];
    cards.forEach(([t, d, col], i) => {
        const x = 0.4 + i * 4.13;
        slide.addShape(pptx.ShapeType.rect, { x, y: 1.8, w: 3.85, h: 2.6, fill: { color: C.slateLt }, line: { color: col, pt: 1.5 } });
        slide.addShape(pptx.ShapeType.rect, { x, y: 1.8, w: 3.85, h: 0.12, fill: { color: col } });
        slide.addText(t, { x: x + 0.2, y: 2.05, w: 3.45, h: 0.8, fontSize: 16, bold: true, color: C.navy, fontFace: FONT });
        slide.addText(d, { x: x + 0.2, y: 2.85, w: 3.45, h: 1.4, fontSize: 13, color: C.dark, fontFace: FONT, valign: "top" });
    });

    slide.addShape(pptx.ShapeType.rect, { x: 0.4, y: 4.8, w: 12.2, h: 1.4, fill: { color: C.greenLt }, line: { color: C.green, pt: 1.5 } });
    slide.addText("One line to open with", { x: 0.65, y: 4.9, w: 11.8, h: 0.4, fontSize: 14, bold: true, color: C.greenDk, fontFace: FONT });
    slide.addText("“These updates take the two things that slow us down — receiving at the counter, and answering ‘how are we doing?’ — and make both a scan or a click away.”", {
        x: 0.65, y: 5.3, w: 11.8, h: 0.85, fontSize: 15, italic: true, color: C.navy, fontFace: FONT, valign: "top",
    });
    slide.addText("Thank you!", { x: 0.4, y: 6.35, w: 12.2, h: 0.45, fontSize: 20, bold: true, color: C.navy, fontFace: FONT, align: "center" });
    slide.addNotes("Close on the value to the user, not the feature list. Invite questions. Offer to swap sample report numbers for real ones from their office.");
}

// ── Save ──────────────────────────────────────────────────────────────────────
const OUT_FILE = process.env.PPTX_OUT || "docs/DTIS-Updates.pptx";
pptx.writeFile({ fileName: OUT_FILE })
    .then(() => console.log("✅  Saved: " + OUT_FILE))
    .catch((e) => { console.error("❌  Error:", e); process.exit(1); });

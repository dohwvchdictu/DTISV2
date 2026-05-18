const PptxGenJS = require("pptxgenjs");

const pptx = new PptxGenJS();
pptx.layout = "LAYOUT_WIDE";

// ── Theme ────────────────────────────────────────────────────────────────────
const C = {
    navy:    "1B3A5C",
    blue:    "2563EB",
    accent:  "3B82F6",
    light:   "EFF6FF",
    white:   "FFFFFF",
    gray:    "6B7280",
    dark:    "1F2937",
    green:   "16A34A",
    amber:   "D97706",
    red:     "DC2626",
};

const FONT = "Calibri";

// ── Helpers ──────────────────────────────────────────────────────────────────
function addSlide(opts = {}) {
    const slide = pptx.addSlide();
    // default white background
    slide.background = { color: opts.bg || C.white };
    return slide;
}

function headerBar(slide, title, subtitle) {
    // navy bar at top
    slide.addShape(pptx.ShapeType.rect, {
        x: 0, y: 0, w: "100%", h: 1.4,
        fill: { color: C.navy },
    });
    slide.addText(title, {
        x: 0.4, y: 0.12, w: 9, h: 0.8,
        fontSize: 24, bold: true, color: C.white, fontFace: FONT,
    });
    if (subtitle) {
        slide.addText(subtitle, {
            x: 0.4, y: 0.88, w: 9, h: 0.45,
            fontSize: 13, color: C.accent, fontFace: FONT, italic: true,
        });
    }
    // accent line
    slide.addShape(pptx.ShapeType.rect, {
        x: 0, y: 1.4, w: "100%", h: 0.06,
        fill: { color: C.accent },
    });
}

function footerBar(slide, text = "DTIS End-User Training") {
    slide.addShape(pptx.ShapeType.rect, {
        x: 0, y: 6.9, w: "100%", h: 0.38,
        fill: { color: C.navy },
    });
    slide.addText(text, {
        x: 0.3, y: 6.95, w: 9.5, h: 0.28,
        fontSize: 9, color: C.white, fontFace: FONT,
    });
}

function bodyText(slide, lines, opts = {}) {
    const rows = lines.map((line) => {
        if (typeof line === "string") {
            return { text: line, options: { fontSize: opts.fontSize || 16, color: opts.color || C.dark, fontFace: FONT } };
        }
        return line;
    });
    slide.addText(rows, {
        x: opts.x || 0.4, y: opts.y || 1.6,
        w: opts.w || 12.2, h: opts.h || 4.6,
        bullet: opts.bullet !== false ? { type: "bullet", characterCode: "25CF", color: C.accent } : false,
        valign: "top",
        ...opts.extra,
    });
}

function noteText(slide, text) {
    slide.addNotes(text);
}

function tableSlide(slide, head, rows, y = 1.7) {
    const colW = 12.2 / head.length;
    const tableRows = [
        head.map((h) => ({
            text: h,
            options: { bold: true, color: C.white, fill: { color: C.navy }, fontSize: 13, fontFace: FONT, align: "center" },
        })),
        ...rows.map((row, ri) =>
            row.map((cell) => ({
                text: cell,
                options: {
                    color: C.dark,
                    fill: { color: ri % 2 === 0 ? C.light : C.white },
                    fontSize: 12, fontFace: FONT,
                },
            }))
        ),
    ];
    slide.addTable(tableRows, {
        x: 0.4, y, w: 12.2,
        colW: head.map(() => colW),
        border: { type: "solid", color: "D1D5DB", pt: 0.5 },
        rowH: 0.45,
    });
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 1 — Title
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide({ bg: C.navy });
    slide.addShape(pptx.ShapeType.rect, { x: 0, y: 0, w: "100%", h: "100%", fill: { color: C.navy } });
    slide.addShape(pptx.ShapeType.rect, { x: 0, y: 4.8, w: "100%", h: 2.5, fill: { color: C.blue } });

    slide.addText("Document Tracking\nInformation System", {
        x: 0.8, y: 0.9, w: 11.4, h: 2.6,
        fontSize: 40, bold: true, color: C.white, fontFace: FONT, align: "center",
    });
    slide.addText("DTIS", {
        x: 0.8, y: 0.2, w: 11.4, h: 0.7,
        fontSize: 18, color: C.accent, fontFace: FONT, align: "center", bold: true,
    });
    slide.addText("End-User Training", {
        x: 0.8, y: 5.1, w: 11.4, h: 0.65,
        fontSize: 24, color: C.white, fontFace: FONT, align: "center",
    });
    slide.addText("[Office / Agency Name]  |  [Date]  |  Trainer: [Name]", {
        x: 0.8, y: 5.85, w: 11.4, h: 0.45,
        fontSize: 13, color: C.light, fontFace: FONT, align: "center",
    });
    noteText(slide, "Welcome participants. Introduce yourself. Ask how many have used a document tracking system before. Set the tone: this is a hands-on session — they will be clicking through the system themselves by the end.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 2 — Agenda
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "What We Will Cover Today", "Training Agenda");
    footerBar(slide);
    bodyText(slide, [
        "What is DTIS and why we use it",
        "Key terms and concepts",
        "System walkthrough — live demonstration",
        "Hands-on practice (you will use the system)",
        "Q&A and assessment",
        "Quick reference and support",
    ], { y: 1.65, h: 4.4, fontSize: 18 });
    slide.addText("Total time: ~2.5 hours", {
        x: 0.4, y: 6.4, w: 12.2, h: 0.4,
        fontSize: 12, color: C.gray, fontFace: FONT, italic: true,
    });
    noteText(slide, "Walk through the agenda. Let them know there is a hands-on portion — they should have their login credentials ready. Remind them to ask questions at any time.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 3 — What is DTIS
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "What is DTIS?", "Document Tracking Information System");
    footerBar(slide);
    bodyText(slide, [
        "A digital system for tracking documents across offices",
        "Replaces manual logbooks and paper routing slips",
        "Every document gets a unique Control Number",
        "Real-time visibility — anyone with access can see where a document is right now",
    ], { y: 1.65, h: 3.6, fontSize: 18 });
    slide.addShape(pptx.ShapeType.rect, {
        x: 0.4, y: 5.3, w: 12.2, h: 0.9,
        fill: { color: C.light }, line: { color: C.accent, pt: 1 },
    });
    slide.addText('Have you ever asked "where is that document?" and nobody knew?\nDTIS answers that question — instantly.', {
        x: 0.6, y: 5.35, w: 12, h: 0.8,
        fontSize: 13, color: C.blue, fontFace: FONT, italic: true, bold: true,
    });
    noteText(slide, "Emphasize the pain point it solves: 'Have you ever asked where is that document and nobody knew? DTIS answers that question instantly.'");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 4 — Why DTIS
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "Why DTIS Matters");
    footerBar(slide);
    bodyText(slide, [
        "Tracks every document movement — no more lost files",
        "Records who received, processed, and forwarded each document",
        "Measures turnaround time per the Citizen Charter (Anti-Red Tape Act)",
        "Generates reports for compliance and performance monitoring",
        "Creates a full audit trail — every action is logged permanently",
    ], { y: 1.65, h: 4.4, fontSize: 18 });
    noteText(slide, "If your agency is subject to ARTA, highlight that DTIS helps flag time-sensitive documents and measure compliance. This is not optional — it is part of how the agency is evaluated.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 5 — Control Number
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "The Control Number", "Key Concept #1");
    footerBar(slide);
    bodyText(slide, [
        "Every document in DTIS has a unique Control Number",
        "Generated automatically — you do not type it",
        "Use it to search for any document instantly",
        "Print it on the physical transmittal form",
    ], { y: 1.65, h: 2.9, fontSize: 18 });

    // control number box
    slide.addShape(pptx.ShapeType.rect, {
        x: 1.5, y: 4.6, w: 10, h: 1.5,
        fill: { color: C.navy }, line: { color: C.accent, pt: 2 },
    });
    slide.addText("Example Control Number", {
        x: 1.5, y: 4.65, w: 10, h: 0.4,
        fontSize: 11, color: C.accent, fontFace: FONT, align: "center",
    });
    slide.addText("DC19120240151437", {
        x: 1.5, y: 5.0, w: 10, h: 0.9,
        fontSize: 32, bold: true, color: C.white, fontFace: "Courier New", align: "center",
    });
    noteText(slide, "The control number is the most important thing a staff member needs to remember. If someone calls asking about a document, the first thing to ask them is 'what is your control number?'");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 6 — Document Statuses
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "Document Statuses", "Key Concept #2 — What They Mean");
    footerBar(slide);
    tableSlide(slide,
        ["Status", "Meaning"],
        [
            ["For Receiving",  "Document has been sent — waiting for the next office to receive it"],
            ["On Process",     "The office has received it and is currently working on it"],
            ["Endorsed",       "The document has been approved or signed off"],
            ["Forwarded",      "Document has been sent to another office for processing"],
            ["Closed",         "Document is completed — no further action needed"],
            ["Returned",       "Document was sent back for revision or correction"],
        ],
        1.65
    );
    noteText(slide, "These statuses change automatically as staff take actions. You do not manually type a status — it updates when you click Receive, Forward, Endorse, or Close.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 7 — The Document Log
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "The Document Log", "Key Concept #3 — Full Audit Trail");
    footerBar(slide);
    bodyText(slide, [
        "Every action taken on a document is recorded automatically",
        "Log shows: who did it, what they did, when, and from which office",
        "Cannot be edited or deleted — permanent record",
        "Visible at the bottom of every document detail page",
    ], { y: 1.65, h: 3.2, fontSize: 18 });
    slide.addShape(pptx.ShapeType.rect, {
        x: 0.4, y: 4.9, w: 12.2, h: 1.3,
        fill: { color: C.light }, line: { color: C.accent, pt: 1 },
    });
    slide.addText("Every click you make is recorded with your name attached.\nThe log is the source of truth if there is ever a question about a document.", {
        x: 0.65, y: 4.98, w: 11.8, h: 1.1,
        fontSize: 14, color: C.navy, fontFace: FONT, bold: true, italic: true,
    });
    noteText(slide, "This is what makes DTIS a compliance tool, not just a tracker. Stress that every click they make is recorded with their name attached.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 8 — Logging In
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "How to Log In");
    footerBar(slide);
    const steps = [
        { text: "1", options: { fontSize: 16, bold: true, color: C.white, fontFace: FONT } },
        { text: "  Open your browser and go to the DTIS URL", options: { fontSize: 16, color: C.dark, fontFace: FONT } },
    ];
    bodyText(slide, [
        "Open your browser and go to the DTIS URL:  [insert URL here]",
        "Enter your email address and password",
        "  → Same credentials used in your HR system",
        "Click Log In",
        "If you see an error: check your email spelling or contact IT",
        "Sessions expire — if idle too long, you will be redirected to login",
    ], { y: 1.65, h: 4.6, fontSize: 17 });
    noteText(slide, "Credentials come from the HR system — DTIS does not have separate passwords. If they cannot log in, it is likely a credentials issue. Direct them to IT, not the trainer.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 9 — Dashboard
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "Your Dashboard", "The Starting Point — Check It Every Morning");
    footerBar(slide);

    const boxes = [
        { label: "Incoming", color: C.blue,  x: 0.5 },
        { label: "Pending",  color: C.amber, x: 4.3 },
        { label: "Closed",   color: C.green, x: 8.1 },
    ];
    boxes.forEach((b) => {
        slide.addShape(pptx.ShapeType.rect, {
            x: b.x, y: 1.75, w: 3.5, h: 1.8,
            fill: { color: b.color }, line: { color: b.color, pt: 0 },
        });
        slide.addText(b.label, {
            x: b.x, y: 1.85, w: 3.5, h: 0.55,
            fontSize: 18, bold: true, color: C.white, fontFace: FONT, align: "center",
        });
        slide.addText("(count)", {
            x: b.x, y: 2.45, w: 3.5, h: 0.9,
            fontSize: 28, color: C.white, fontFace: FONT, align: "center", bold: true,
        });
    });

    slide.addText("Processing Rate:", {
        x: 0.5, y: 3.75, w: 3.2, h: 0.4,
        fontSize: 14, color: C.dark, fontFace: FONT, bold: true,
    });
    slide.addShape(pptx.ShapeType.rect, { x: 3.7, y: 3.82, w: 8.5, h: 0.28, fill: { color: "E5E7EB" } });
    slide.addShape(pptx.ShapeType.rect, { x: 3.7, y: 3.82, w: 6.4, h: 0.28, fill: { color: C.green } });
    slide.addText("75%", { x: 10.3, y: 3.78, w: 1.8, h: 0.36, fontSize: 14, color: C.green, fontFace: FONT, bold: true });

    bodyText(slide, [
        "Numbers shown are specific to YOUR office",
        "Higher processing rate = better performance",
    ], { y: 4.3, h: 1.5, fontSize: 16 });
    noteText(slide, "The dashboard is personal to your office. A different office will see different numbers. The processing rate is a performance metric — the higher, the better.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 10 — Creating a Document
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "How to Create a New Document");
    footerBar(slide);

    const steps = [
        ["1", "Click New Document in the sidebar"],
        ["2", "Select Source:  Internal  or  External"],
        ["3", "Select Category  (e.g. Purchase Request, Payment)"],
        ["4", "Type the Subject"],
        ["5", "Check ARTA if the document is time-sensitive"],
        ["6", "Select the office to Forward To"],
        ["7", "Click Submit — control number is generated automatically"],
    ];

    steps.forEach(([num, text], i) => {
        const y = 1.65 + i * 0.67;
        slide.addShape(pptx.ShapeType.ellipse, {
            x: 0.3, y: y + 0.02, w: 0.44, h: 0.44,
            fill: { color: C.blue }, line: { color: C.blue, pt: 0 },
        });
        slide.addText(num, {
            x: 0.3, y: y + 0.02, w: 0.44, h: 0.44,
            fontSize: 14, bold: true, color: C.white, fontFace: FONT, align: "center", valign: "middle",
        });
        slide.addText(text, {
            x: 0.9, y, w: 11.7, h: 0.55,
            fontSize: 16, color: C.dark, fontFace: FONT,
        });
    });
    noteText(slide, "Walk through a real example. Pick a common document type in their office (e.g. a purchase request) and fill out the form together on screen. The system generates the control number automatically.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 11 — Document Fields Explained
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "Understanding the Form Fields");
    footerBar(slide);
    tableSlide(slide,
        ["Field", "What to Enter"],
        [
            ["Source",      "Internal = from within the agency;  External = from outside (public or another agency)"],
            ["Category",    "Type of document — matches the physical document type (e.g. Purchase Request)"],
            ["Subject",     "Brief description of what the document is about"],
            ["ARTA",        "Check if the document falls under Anti-Red Tape Act coverage"],
            ["Forward To",  "The office that should receive and process this document next"],
        ],
        1.65
    );
    noteText(slide, "Walk through a real example. Pick a common document type in their office and fill out the form together on screen.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 12 — Receiving an Incoming Document
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "How to Receive a Document");
    footerBar(slide);

    const steps = [
        ["1", "Click  Status → Incoming  in the sidebar"],
        ["2", "Find the document — search by control number or subject"],
        ["3", "Click the document to open it"],
        ["4", "Click  Receive"],
        ["5", "Status changes to  On Process"],
        ["6", "Document now appears in your  Pending  list"],
    ];

    steps.forEach(([num, text], i) => {
        const y = 1.65 + i * 0.72;
        slide.addShape(pptx.ShapeType.ellipse, {
            x: 0.3, y: y + 0.02, w: 0.44, h: 0.44,
            fill: { color: C.green }, line: { color: C.green, pt: 0 },
        });
        slide.addText(num, {
            x: 0.3, y: y + 0.02, w: 0.44, h: 0.44,
            fontSize: 14, bold: true, color: C.white, fontFace: FONT, align: "center", valign: "middle",
        });
        slide.addText(text, {
            x: 0.9, y, w: 11.7, h: 0.6,
            fontSize: 16, color: C.dark, fontFace: FONT,
        });
    });
    noteText(slide, "Until a document is received, it stays in 'For Receiving' status. The sending office can see it is still waiting. Receiving it is how you acknowledge it is now in your hands.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 13 — Status Views
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "Navigating the Status Views", "Think of these as folders in your inbox");
    footerBar(slide);
    tableSlide(slide,
        ["Status View", "What It Contains", "Action Needed?"],
        [
            ["Incoming",   "Documents sent TO your office — not yet received",    "YES — Receive them"],
            ["Pending",    "Documents currently being processed by your office",   "YES — Process them"],
            ["Endorsed",   "Documents your office has endorsed / approved",        "No"],
            ["Forwarded",  "Documents your office has sent to another office",     "No"],
            ["Closed",     "Completed documents — read only",                      "No"],
        ],
        1.65
    );
    noteText(slide, "Your daily workflow starts with Incoming, moves to Pending, and ends with Forwarded or Closed.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 14 — Document Lifecycle
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "How a Document Moves Through the System", "Document Lifecycle");
    footerBar(slide);

    // draw flow boxes
    const flow = [
        { label: "Office A\nCreates Doc",     color: C.blue,  x: 0.3,  y: 2.0  },
        { label: "For\nReceiving",             color: C.amber, x: 2.7,  y: 2.0  },
        { label: "Office B\nReceives",         color: C.blue,  x: 5.1,  y: 2.0  },
        { label: "On\nProcess",                color: C.amber, x: 7.5,  y: 2.0  },
        { label: "Forward\nto Office C",       color: C.navy,  x: 9.9,  y: 2.0  },
    ];
    flow.forEach((f) => {
        slide.addShape(pptx.ShapeType.rect, {
            x: f.x, y: f.y, w: 2.1, h: 1.2,
            fill: { color: f.color }, line: { color: f.color, pt: 0 },
        });
        slide.addText(f.label, {
            x: f.x, y: f.y, w: 2.1, h: 1.2,
            fontSize: 13, bold: true, color: C.white, fontFace: FONT, align: "center", valign: "middle",
        });
    });

    // arrows between boxes
    [0.3, 2.7, 5.1, 7.5].forEach((x) => {
        slide.addShape(pptx.ShapeType.rect, {
            x: x + 2.1, y: 2.53, w: 0.6, h: 0.12,
            fill: { color: C.gray }, line: { color: C.gray, pt: 0 },
        });
    });

    // second row
    const flow2 = [
        { label: "For\nReceiving",    color: C.amber, x: 9.9,  y: 3.6 },
        { label: "Office C\nReceives", color: C.blue, x: 7.5,  y: 3.6 },
        { label: "On\nProcess",        color: C.amber, x: 5.1,  y: 3.6 },
        { label: "Closed",             color: C.green, x: 2.7,  y: 3.6 },
    ];
    flow2.forEach((f) => {
        slide.addShape(pptx.ShapeType.rect, {
            x: f.x, y: f.y, w: 2.1, h: 1.2,
            fill: { color: f.color }, line: { color: f.color, pt: 0 },
        });
        slide.addText(f.label, {
            x: f.x, y: f.y, w: 2.1, h: 1.2,
            fontSize: 13, bold: true, color: C.white, fontFace: FONT, align: "center", valign: "middle",
        });
    });
    // down arrow
    slide.addShape(pptx.ShapeType.rect, {
        x: 10.9, y: 3.2, w: 0.12, h: 0.4,
        fill: { color: C.gray }, line: { color: C.gray, pt: 0 },
    });

    slide.addText("A document can be forwarded multiple times before it is closed. Each movement is recorded in the log.", {
        x: 0.4, y: 5.0, w: 12.2, h: 0.5,
        fontSize: 14, color: C.gray, fontFace: FONT, italic: true,
    });
    noteText(slide, "Walk through the diagram step by step. Ask: 'If Office B forwards to Office C, does Office A know?' — Yes. They can look up the control number and see it was forwarded.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 15 — Forwarding
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "How to Forward a Document");
    footerBar(slide);
    const steps = [
        ["1", "Open the document from your  Pending  list"],
        ["2", "Click  Forward"],
        ["3", "Select the office to forward to"],
        ["4", "Add Remarks  (recommended — e.g. 'Forwarded for signature')"],
        ["5", "Click  Confirm"],
        ["6", "Document appears in the selected office's  Incoming  list"],
    ];
    steps.forEach(([num, text], i) => {
        const y = 1.65 + i * 0.72;
        slide.addShape(pptx.ShapeType.ellipse, {
            x: 0.3, y: y + 0.02, w: 0.44, h: 0.44,
            fill: { color: C.navy }, line: { color: C.navy, pt: 0 },
        });
        slide.addText(num, {
            x: 0.3, y: y + 0.02, w: 0.44, h: 0.44,
            fontSize: 14, bold: true, color: C.white, fontFace: FONT, align: "center", valign: "middle",
        });
        slide.addText(text, {
            x: 0.9, y, w: 11.7, h: 0.6,
            fontSize: 16, color: C.dark, fontFace: FONT,
        });
    });
    noteText(slide, "Forwarding is the most common action. Stress that they should add remarks — future readers of the log will thank them.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 16 — Endorse vs Close
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "Endorse vs. Close — What Is the Difference?");
    footerBar(slide);

    // Endorse box
    slide.addShape(pptx.ShapeType.rect, { x: 0.4, y: 1.65, w: 5.9, h: 4.2, fill: { color: C.light }, line: { color: C.accent, pt: 2 } });
    slide.addText("ENDORSE", { x: 0.4, y: 1.65, w: 5.9, h: 0.6, fontSize: 18, bold: true, color: C.white, fontFace: FONT, align: "center", fill: { color: C.blue } });
    slide.addText([
        { text: "Document has been approved or signed off\n", options: { fontSize: 15, color: C.dark, fontFace: FONT } },
        { text: "Document continues — may still move to other offices\n\n", options: { fontSize: 15, color: C.dark, fontFace: FONT } },
        { text: "Use when: ", options: { fontSize: 15, bold: true, color: C.navy, fontFace: FONT } },
        { text: "you are approving but the document is not yet complete", options: { fontSize: 15, color: C.dark, fontFace: FONT } },
    ], { x: 0.6, y: 2.35, w: 5.5, h: 3.3, valign: "top" });

    // Close box
    slide.addShape(pptx.ShapeType.rect, { x: 6.7, y: 1.65, w: 5.9, h: 4.2, fill: { color: C.light }, line: { color: C.green, pt: 2 } });
    slide.addText("CLOSE", { x: 6.7, y: 1.65, w: 5.9, h: 0.6, fontSize: 18, bold: true, color: C.white, fontFace: FONT, align: "center", fill: { color: C.green } });
    slide.addText([
        { text: "Document is fully processed — no more action needed\n", options: { fontSize: 15, color: C.dark, fontFace: FONT } },
        { text: "Document becomes read-only after closing\n\n", options: { fontSize: 15, color: C.dark, fontFace: FONT } },
        { text: "Use when: ", options: { fontSize: 15, bold: true, color: C.navy, fontFace: FONT } },
        { text: "the document has reached its final destination", options: { fontSize: 15, color: C.dark, fontFace: FONT } },
    ], { x: 6.9, y: 2.35, w: 5.5, h: 3.3, valign: "top" });
    noteText(slide, "A common mistake is closing a document that still needs to move. Remind them: only close when the document's journey is truly over.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 17 — Bundles
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "Working with Bundles", "Grouping Related Documents Together");
    footerBar(slide);
    bodyText(slide, [
        "Use New Bundle when several documents belong to the same transaction",
        "Attach individual documents to the bundle",
        "The bundle is routed as one unit — all documents move together",
        "Example: a Purchase Request with three supporting attachments",
    ], { y: 1.65, h: 2.8, fontSize: 18 });

    slide.addShape(pptx.ShapeType.rect, { x: 0.4, y: 4.55, w: 12.2, h: 1.6, fill: { color: C.light }, line: { color: C.accent, pt: 1 } });
    slide.addText("Steps: ", { x: 0.65, y: 4.62, w: 1.1, h: 0.4, fontSize: 14, bold: true, color: C.navy, fontFace: FONT });
    slide.addText("New Bundle → Fill in details → Attach documents → Forward bundle", {
        x: 0.65, y: 5.05, w: 11.7, h: 0.9,
        fontSize: 16, color: C.dark, fontFace: FONT,
    });
    noteText(slide, "Bundles are for convenience. If you are only routing one document, you do not need a bundle. Think of a bundle like a folder that holds several related documents.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 18 — Transmittal & Logbook
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "Transmittal Form and Logbook", "Bridging Digital and Physical Records");
    footerBar(slide);

    slide.addShape(pptx.ShapeType.rect, { x: 0.4, y: 1.65, w: 5.9, h: 4.2, fill: { color: C.light }, line: { color: C.navy, pt: 2 } });
    slide.addText("TRANSMITTAL FORM", { x: 0.4, y: 1.65, w: 5.9, h: 0.6, fontSize: 16, bold: true, color: C.white, fontFace: FONT, align: "center", fill: { color: C.navy } });
    slide.addText("Printed paper that accompanies the physical document folder between offices.\n\nContains: document details + a barcode\n\nHow to print:\nOpen document → Click Print Transmittal → PDF opens → Print", {
        x: 0.6, y: 2.35, w: 5.5, h: 3.3, fontSize: 14, color: C.dark, fontFace: FONT, valign: "top",
    });

    slide.addShape(pptx.ShapeType.rect, { x: 6.7, y: 1.65, w: 5.9, h: 4.2, fill: { color: C.light }, line: { color: C.navy, pt: 2 } });
    slide.addText("LOGBOOK", { x: 6.7, y: 1.65, w: 5.9, h: 0.6, fontSize: 16, bold: true, color: C.white, fontFace: FONT, align: "center", fill: { color: C.navy } });
    slide.addText("Printable record of all document movements for a given period.\n\nUsed for compliance reporting and records management\n\nHow to generate:\nInbox menu → Generate Logbook → PDF opens", {
        x: 6.9, y: 2.35, w: 5.5, h: 3.3, fontSize: 14, color: C.dark, fontFace: FONT, valign: "top",
    });
    noteText(slide, "The transmittal form bridges the digital and physical world. Every time a physical folder moves from one office to another, the transmittal form goes with it.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 19 — Reports
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "Available Reports", "Live Data — Always Up to Date");
    footerBar(slide);
    tableSlide(slide,
        ["Report", "What It Shows"],
        [
            ["Document Status",        "All documents grouped by their current status"],
            ["Status per Employee",    "Who processed what documents and how many"],
            ["External Documents",     "Documents originating from outside the agency"],
            ["Internal Documents",     "Documents originating from within the agency"],
        ],
        1.65
    );
    bodyText(slide, [
        "Found under the Reports section in the sidebar",
        "All reports can be exported as PDF for printing",
    ], { y: 5.1, h: 1.2, fontSize: 15, bullet: true });
    noteText(slide, "Reports are mainly for supervisors and records officers. Standard staff will rarely need reports. Show where they are but do not spend too much time here unless your audience includes supervisors.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 20 — Hands-On Practice
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "Your Turn — Hands-On Practice", "30–40 minutes");
    footerBar(slide);

    const exercises = [
        ["Exercise 1", "Log in and explore your dashboard"],
        ["Exercise 2", "Create a new internal document (subject of your choice)"],
        ["Exercise 3", "Forward it to a fellow trainee's office"],
        ["Exercise 4", "Receive the document sent to you and mark it On Process"],
        ["Exercise 5", "Close a document"],
        ["Exercise 6\n(Advanced)", "Create a bundle and attach two documents to it"],
    ];

    exercises.forEach(([label, text], i) => {
        const y = 1.65 + i * 0.72;
        slide.addShape(pptx.ShapeType.rect, {
            x: 0.3, y, w: 2.0, h: 0.58,
            fill: { color: i === 5 ? C.amber : C.blue }, line: { color: C.blue, pt: 0 },
        });
        slide.addText(label, {
            x: 0.3, y, w: 2.0, h: 0.58,
            fontSize: 12, bold: true, color: C.white, fontFace: FONT, align: "center", valign: "middle",
        });
        slide.addText(text, {
            x: 2.5, y: y + 0.06, w: 10.1, h: 0.5,
            fontSize: 16, color: C.dark, fontFace: FONT,
        });
    });
    slide.addText("Raise your hand if you need help — trainer is available", {
        x: 0.4, y: 6.45, w: 12.2, h: 0.35,
        fontSize: 12, color: C.gray, fontFace: FONT, italic: true,
    });
    noteText(slide, "Give 30–40 minutes for this. Walk around the room. Do NOT take the mouse from trainees — guide them verbally. Note the most common mistakes to address in the Q&A.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 21 — Review Q&A
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "Quick Check — What Did We Learn?", "Review Questions");
    footerBar(slide);

    const qa = [
        ["Q1", "Where do you go to receive a document sent to your office?",          "Status → Incoming"],
        ["Q2", "What does the control number tell you?",                              "Unique ID — office, user, and date created"],
        ["Q3", "Difference between Forward and Endorse?",                             "Forward = send elsewhere;  Endorse = approve/sign off"],
        ["Q4", "When do you use a Bundle?",                                           "When multiple documents belong to the same transaction"],
        ["Q5", "Where can you see who last acted on a document?",                     "Log / History section at the bottom of the detail page"],
    ];

    qa.forEach(([num, q, a], i) => {
        const y = 1.65 + i * 0.94;
        slide.addText(num, {
            x: 0.3, y, w: 0.55, h: 0.42,
            fontSize: 14, bold: true, color: C.white, fontFace: FONT, align: "center",
            fill: { color: C.blue }, valign: "middle",
        });
        slide.addText(q, {
            x: 0.95, y, w: 11.6, h: 0.42,
            fontSize: 15, color: C.dark, fontFace: FONT, bold: true,
        });
        slide.addText("→  " + a, {
            x: 1.1, y: y + 0.44, w: 11.5, h: 0.38,
            fontSize: 14, color: C.green, fontFace: FONT,
        });
    });
    noteText(slide, "Ask these as a group. Let trainees answer before showing the answer. This is not a formal test — it is a discussion to reinforce key points.");
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDE 22 — Closing
// ════════════════════════════════════════════════════════════════════════════
{
    const slide = addSlide();
    headerBar(slide, "You Are Ready — What's Next?");
    footerBar(slide);

    slide.addText("Daily reminders:", {
        x: 0.4, y: 1.65, w: 12.2, h: 0.45,
        fontSize: 16, bold: true, color: C.navy, fontFace: FONT,
    });
    bodyText(slide, [
        "Check your Incoming list every morning",
        "Always add Remarks when forwarding",
        "The control number is your document's ID — keep track of it",
        "Every action is logged — be accurate",
    ], { y: 2.1, h: 2.2, fontSize: 16 });

    slide.addShape(pptx.ShapeType.rect, { x: 0.4, y: 4.4, w: 12.2, h: 1.8, fill: { color: C.light }, line: { color: C.accent, pt: 1 } });
    slide.addText("Support Contacts", { x: 0.65, y: 4.48, w: 6, h: 0.4, fontSize: 15, bold: true, color: C.navy, fontFace: FONT });
    slide.addText("Technical issues:    [IT Helpdesk / contact info]\nSystem questions:  [DTIS Administrator / contact info]", {
        x: 0.65, y: 4.92, w: 12, h: 1.0, fontSize: 14, color: C.dark, fontFace: FONT,
    });

    slide.addText("Thank you!", {
        x: 0.4, y: 6.3, w: 12.2, h: 0.5,
        fontSize: 22, bold: true, color: C.navy, fontFace: FONT, align: "center",
    });
    noteText(slide, "Distribute the quick reference card. Collect attendance sheet. Remind them that every click they make is recorded. Encourage them to start using the system today, not 'when they feel ready.'");
}

// ════════════════════════════════════════════════════════════════════════════
// Save
// ════════════════════════════════════════════════════════════════════════════
pptx.writeFile({ fileName: "docs/DTIS-Training.pptx" })
    .then(() => console.log("✅  Saved: docs/DTIS-Training.pptx"))
    .catch((e) => { console.error("❌  Error:", e); process.exit(1); });

/* ── CSRF token ─────────────────────────────────────────────────────── */
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
let controller = null;

/* ── Toast helper ───────────────────────────────────────────────────── */
function toast(msg, type = "") {
    const el = document.getElementById("toast");
    el.textContent = msg;
    el.className = "toast-bar show " + type;
    clearTimeout(el._t);
    el._t = setTimeout(() => (el.className = "toast-bar"), 3800);
}

/* ── Start Fetch ────────────────────────────────────────────────────── */
async function startFetch() {
    const categories = document.getElementById("catInput").value.trim();

    if (!categories) {
        toast("⚠️ Please enter categories", "error");
        return;
    }

    const startBtn = document.getElementById("fetchBtn");
    const stopBtn = document.getElementById("stopBtn");

    controller = new AbortController();

    startBtn.disabled = true;
    stopBtn.disabled = false;

    startBtn.innerHTML = '<span class="spinner"></span> Fetching...';

    try {
        const res = await fetch("/fetch", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": CSRF,
                Accept: "application/json",
            },
            body: JSON.stringify({ categories }),
            signal: controller.signal,
        });

        const data = await res.json();

        toast("✓ Done", "success");
    } catch (err) {
        if (err.name === "AbortError") {
            toast("⛔ Stopped", "error");
        } else {
            toast(err.message, "error");
        }
    } finally {
        startBtn.disabled = false;
        stopBtn.disabled = true;

        startBtn.innerHTML = '<i class="fa-solid fa-play"></i> Start Fetching';
        setTimeout(() => location.reload(), 800);
    }
}

function stopFetch() {
    if (controller) {
        controller.abort();
    }
}

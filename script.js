const faces = ['U', 'D', 'L', 'R', 'F', 'B'];
const modifiers = ['', "'", '2'];

function getAxis(f) {
    if (f === 'U' || f === 'D') return 0;
    if (f === 'L' || f === 'R') return 1;
    return 2;
}

function generateScramble() {
    const moves = [];
    let last = null;
    let secondLast = null;

    for (let i = 0; i < 20; i++) {
        let face;
        do {
            face = faces[Math.floor(Math.random() * faces.length)];
        } while (
            face === last ||
            (last && secondLast && getAxis(face) === getAxis(last) && face === secondLast)
        );

        const mod = modifiers[Math.floor(Math.random() * modifiers.length)];
        moves.push(face + mod);
        secondLast = last;
        last = face;
    }

    return moves.join(' ');
}

let currentScramble = '';

function setScrambleBaru() {
    currentScramble = generateScramble();
    const scrambleEl = document.getElementById('scramble-display');
    if (scrambleEl) {
        scrambleEl.textContent = currentScramble;
    }
}

let state = 'IDLE';
let holdTimer = null;
let startTime = 0;
let rafId = null;

const timerEl        = document.getElementById('timer-display');
const diffEl         = document.getElementById('timer-diff');
const statusEl       = document.getElementById('timer-status');
const timerContainer = document.getElementById('timer-container');
const timerSection   = document.getElementById('timer-section');

function setTimerVisualState(s) {
    if (!timerContainer) return;
    timerContainer.classList.remove('timer-holding', 'timer-ready', 'timer-running');
    if (timerSection) {
        timerSection.classList.remove('is-holding', 'is-ready', 'is-running');
    }

    if (s === 'HOLDING') {
        timerContainer.classList.add('timer-holding');
        if (timerSection) timerSection.classList.add('is-holding');
    } else if (s === 'READY') {
        timerContainer.classList.add('timer-ready');
        if (timerSection) timerSection.classList.add('is-ready');
    } else if (s === 'RUNNING') {
        timerContainer.classList.add('timer-running');
        if (timerSection) timerSection.classList.add('is-running');
        document.body.classList.add('timer-active');
    } else {
        document.body.classList.remove('timer-active');
    }
}

function isTyping() {
    const el = document.activeElement;
    return el && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT');
}

function updateTimer() {
    if (state !== 'RUNNING') return;
    const sec = (performance.now() - startTime) / 1000;
    if (timerEl) {
        timerEl.textContent = sec.toFixed(2);
    }
    rafId = requestAnimationFrame(updateTimer);
}

function finishSolve() {
    cancelAnimationFrame(rafId);
    let finalSec = (performance.now() - startTime) / 1000;
    if (finalSec < 0.01) finalSec = 0.01;

    const formatted = finalSec.toFixed(2);
    if (timerEl) timerEl.textContent = formatted;
    state = 'STOPPED';
    setTimerVisualState('STOPPED');

    document.getElementById('input-raw-time').value = formatted;
    document.getElementById('input-scramble').value = currentScramble;
    document.getElementById('form-create-solve').submit();
}

window.addEventListener('keydown', function (e) {
    const modal = document.getElementById('modal-edit');
    if (isTyping() || (modal && modal.open)) return;

    if (state === 'RUNNING') {
        e.preventDefault();
        finishSolve();
        return;
    }

    if (e.code === 'Space') {
        e.preventDefault();
        if (state === 'IDLE' && !e.repeat) {
            state = 'HOLDING';
            setTimerVisualState('HOLDING');
            if (timerEl) timerEl.textContent = '0.00';
            if (diffEl) diffEl.textContent = '';
            if (statusEl) statusEl.textContent = 'Tahan Spacebar...';

            holdTimer = setTimeout(function () {
                if (state === 'HOLDING') {
                    state = 'READY';
                    setTimerVisualState('READY');
                    if (statusEl) statusEl.textContent = 'SIAP! Lepas Spacebar!';
                }
            }, 250);
        }
    }
});

window.addEventListener('keyup', function (e) {
    const modal = document.getElementById('modal-edit');
    if (isTyping() || (modal && modal.open)) return;

    if (e.code === 'Space') {
        e.preventDefault();
        if (state === 'HOLDING') {
            clearTimeout(holdTimer);
            state = 'IDLE';
            setTimerVisualState('IDLE');
            if (statusEl) statusEl.textContent = 'Tahan Spacebar lalu lepas untuk mulai stopwatch';
        } else if (state === 'READY') {
            state = 'RUNNING';
            setTimerVisualState('RUNNING');
            startTime = performance.now();
            if (statusEl) statusEl.textContent = 'Berjalan... Tekan tombol apapun untuk berhenti';
            rafId = requestAnimationFrame(updateTimer);
        } else if (state === 'STOPPED') {
            state = 'IDLE';
            setTimerVisualState('IDLE');
        }
    }
});

// Click / Touch support on Timer Area
if (timerSection) {
    timerSection.addEventListener('pointerdown', function (e) {
        if (e.target.closest('button, a, input')) return;
        if (state === 'RUNNING') {
            finishSolve();
            return;
        }
        if (state === 'IDLE') {
            state = 'HOLDING';
            setTimerVisualState('HOLDING');
            if (timerEl) timerEl.textContent = '0.00';
            if (diffEl) diffEl.textContent = '';
            if (statusEl) statusEl.textContent = 'Tahan...';

            holdTimer = setTimeout(function () {
                if (state === 'HOLDING') {
                    state = 'READY';
                    setTimerVisualState('READY');
                    if (statusEl) statusEl.textContent = 'SIAP! Lepas untuk mulai!';
                }
            }, 250);
        }
    });

    window.addEventListener('pointerup', function () {
        if (state === 'HOLDING') {
            clearTimeout(holdTimer);
            state = 'IDLE';
            setTimerVisualState('IDLE');
            if (statusEl) statusEl.textContent = 'Tahan Spacebar lalu lepas untuk mulai stopwatch';
        } else if (state === 'READY') {
            state = 'RUNNING';
            setTimerVisualState('RUNNING');
            startTime = performance.now();
            if (statusEl) statusEl.textContent = 'Berjalan... Klik/tekan tombol untuk berhenti';
            rafId = requestAnimationFrame(updateTimer);
        }
    });
}

function bukaEdit(id, no, displayTime, penalty, scramble, date) {
    document.getElementById('modal-title').textContent = 'Solves No. #' + no;
    document.getElementById('modal-time').textContent = displayTime;
    document.getElementById('modal-id').value = id;
    document.getElementById('modal-scramble').value = scramble;
    document.getElementById('modal-date').value = date;

    document.getElementById('p-ok').checked = (penalty === 'OK');
    document.getElementById('p-plus2').checked = (penalty === '+2');
    document.getElementById('p-dnf').checked = (penalty === 'DNF');

    document.getElementById('modal-delete').onclick = function () {
        hapusSolve(id, no);
    };

    const linkEdit = document.getElementById('modal-edit-page');
    if (linkEdit) {
        linkEdit.href = 'edit.php?id=' + id;
    }

    const modal = document.getElementById('modal-edit');
    if (modal) {
        modal.showModal();
    }
}

function hapusSolve(id, no) {
    if (confirm('Hapus Catatan Solve No. #' + no + '?')) {
        window.location.href = 'delete.php?id=' + id;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    setScrambleBaru();
    const btnNew = document.getElementById('btn-new-scramble');
    if (btnNew) {
        btnNew.addEventListener('click', function () {
            setScrambleBaru();
            this.blur();
        });
    }
});

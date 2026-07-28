import { HSDropdown } from 'preline';
import './bootstrap';
import 'preline';


/**
 * Bind Preline components that appeared since the last pass.
 *
 * HSStaticMethods.autoInit() walks every registered component's own autoInit, each of
 * which scans the document and constructs instances only for elements not already in
 * its collection. So this is idempotent, and it is what picks up trigger elements a
 * Livewire re-render just inserted -- a [data-hs-overlay] button added by a morph is
 * otherwise never bound and silently does nothing when clicked.
 */
function initPrelineElements() {
    if (window.HSStaticMethods && typeof window.HSStaticMethods.autoInit === 'function') {
        window.HSStaticMethods.autoInit();
    }
}

/**
 * Repair Preline instances whose element survived a morph but whose internal state
 * did not.
 *
 * autoInit() cannot fix these: it dedupes by element reference, and morphdom patches
 * attributes rather than replacing the node, so the stale instance is never rebuilt.
 *
 * The concrete failure: closing a dropdown sets animationInProcess = true and defers
 * clearing it to the menu's transitionend. Livewire then morphs `hidden` back onto the
 * menu, display:none cancels the transition, transitionend never fires, and the flag
 * stays true -- after which every open() returns early and the dropdown looks dead
 * until a full page reload. forceClearState() is Preline's own escape hatch for this.
 */
function repairStalePrelineState() {
    const dropdowns = window.$hsDropdownCollection;

    if (!Array.isArray(dropdowns)) return;

    dropdowns.forEach(({ element }) => {
        if (!element || !element.el || !element.menu) return;

        // Mid-animation but no longer visibly open: the transition was cut short.
        const stuck = element.animationInProcess
            && !element.el.classList.contains('open');

        if (stuck && typeof element.forceClearState === 'function') {
            element.forceClearState();
            element.animationInProcess = false;
        }
    });
}

/**
 * Advanced selects ([data-hs-select]) are deliberately NOT repaired here.
 *
 * An earlier version of this file destroyed instances whose wrapper a morph had pulled
 * apart, expecting autoInit() to rebuild them. That was actively harmful. HSSelect's
 * destroy() ends with:
 *
 *     const t = this.el.parentElement.parentElement;
 *     t.prepend(this.el);
 *     t.querySelector('.hs-select').remove();
 *
 * It assumes the wrapper is still intact -- the exact opposite of the condition that
 * triggered the call. With the wrapper already gone, parentElement.parentElement is the
 * wrong node, so prepend() *relocated the <select>* into a div the server never renders
 * it in, and the querySelector('.hs-select').remove() then threw on null. Livewire's
 * wire:model stayed bound to a displaced element, so choosing an option no longer
 * reached the server and the field failed its 'required' rule.
 *
 * Nothing is needed in its place:
 *   - HSSelect fires a native 'change' on the underlying <select>
 *     (triggerChangeEventForNativeSelect), which is what wire:model listens for.
 *   - Rendering `selected` on the matching <option> server-side makes any rebuild show
 *     the right value.
 *   - Preline's own autoInit() already prunes dead instances via
 *     filter(({element}) => document.contains(element.el)), so a replaced element is
 *     re-initialised without help.
 */

/** Clear stale dropdown state, then let autoInit pick up anything new. Order matters. */
function refreshPreline() {
    repairStalePrelineState();
    initPrelineElements();
}

/**
 * Three separate moments need Preline initialised, and they need different hooks:
 *
 *   hard page load        - Preline's own window-load listener already covers it
 *   wire:navigate         - 'livewire:navigated', below
 *   component re-render   - Livewire's 'morphed' hook, below
 *
 * The middle one is easy to miss. A wire:navigate transition swaps the DOM in place, so
 * window's 'load' event never fires again -- and several Preline components (HSSelect
 * among them) only self-initialise from that event. Arriving at a page through one of the
 * sidebar's wire:navigate links therefore leaves every Preline component unbuilt, which
 * for HSSelect means the <select class="hidden"> it would have replaced stays invisible
 * and the field appears to be missing entirely.
 *
 * Note this listener is NOT { once: true } -- it has to run on every navigation. The
 * 'morphed' hook registration is guarded separately so it is only added once.
 */
let morphedHookRegistered = false;

document.addEventListener('livewire:navigated', () => {
    if (!morphedHookRegistered) {
        morphedHookRegistered = true;

        /**
         * 'morphed' fires once per component update, after the DOM has settled.
         *
         * This replaced an 'element.init' hook, which fired per *element* and ran seven
         * separate autoInit() calls -- each a full-document querySelectorAll -- for every
         * element of every update.
         */
        Livewire.hook('morphed', refreshPreline);
    }

    refreshPreline();
});

// Keyboard shortcut for document search modal
document.addEventListener('keydown', (event) => {
    // Check if '/' key is pressed and not in an input/textarea
    if (event.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
        event.preventDefault();
        const searchModal = document.getElementById('document-search-modal');
        if (searchModal && window.HSOverlay) {
            window.HSOverlay.open(searchModal);
            // Focus on search input after modal opens
            setTimeout(() => {
                const searchInput = searchModal.querySelector('input[type="text"]');
                if (searchInput) {
                    searchInput.focus();
                }
            }, 100);
        }
    }
});
// document.addEventListener('livewire:init', () => {
//     Livewire.on('close-modal', ({ class: className }) => {
//         const modal = document.querySelector(className);
//     });
// });

// document.addEventListener('livewire:init', () => {
//     Livewire.hook('component.init', () => {
//         const dropdown = new HSDropdown(document.querySelector('.hs-dropdown'));
//         const openBtn = document.querySelector('#table-actions');

//         openBtn.addEventListener('click', () => {
//             window.HSDropdown.autoInit()
//             window.HSDropdown.open(dropdown);
//         });
//     });
// });

// document.addEventListener('livewire:load', () => {
//     Livewire.hook('page-changed', () => {
//         const dropdown = new HSDropdown(document.querySelector('.hs-dropdown'));
//         const openBtn = document.querySelector('.hs-dropdown-toggle');

//         openBtn.addEventListener('click', () => {
//             console.log('page-changed');
//         });
//     });

// });

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
 * Discard advanced-select instances whose markup a morph pulled apart, so the autoInit
 * pass that follows rebuilds them from the server's HTML.
 *
 * HSSelect does not merely decorate its <select>: buildWrapper() creates a div, inserts
 * it before the element, then moves the <select> inside it and renders a toggle button
 * and dropdown as siblings. None of that exists in the server's markup, so a Livewire
 * re-render tears the subtree apart and leaves the instance describing a DOM that is no
 * longer there -- the toggle either vanishes or keeps showing a stale title.
 *
 * autoInit() alone will not fix it (it skips elements already in its collection), and
 * unlike a dropdown there is no forceClearState() to call, so the instance has to be
 * destroyed and dropped from the collection first.
 */
function discardDetachedPrelineSelects() {
    if (!Array.isArray(window.$hsSelectCollection)) return;

    const detached = window.$hsSelectCollection.filter(({ element }) => {
        if (!element || !element.el) return true;

        // Healthy state: the select still sits inside the wrapper Preline built for it,
        // and that wrapper is still attached to the document.
        return !element.wrapper
            || !element.wrapper.contains(element.el)
            || !document.contains(element.wrapper);
    });

    if (!detached.length) return;

    detached.forEach((item) => {
        try {
            if (typeof item.element.destroy === 'function') item.element.destroy();
        } catch (e) {
            // Markup was already removed by the morph; nothing left to unbind.
        }
    });

    window.$hsSelectCollection = window.$hsSelectCollection.filter(
        (item) => !detached.includes(item)
    );
}

document.addEventListener('livewire:navigated', () => {
    /**
     * 'morphed' fires once per component update, after the DOM is settled.
     *
     * This previously used 'element.init', which fires per *element* and ran seven
     * separate autoInit() calls -- each a full-document querySelectorAll -- for every
     * element in every update.
     *
     * Order matters: clear out stale instances first, then let autoInit rebuild.
     */
    Livewire.hook('morphed', () => {
        repairStalePrelineState();
        discardDetachedPrelineSelects();
        initPrelineElements();
    });
}, { once: true });

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

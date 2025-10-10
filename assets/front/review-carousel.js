(function () {
    const SELECTORS = {
        root: '[data-review-carousel]',
        track: '[data-review-carousel-track]',
        prev: '[data-review-carousel-prev]',
        next: '[data-review-carousel-next]',
    };

    const ROTATION_INTERVAL = 7000;
    const reduceMotionQuery = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;
    const prefersReducedMotion = () => (reduceMotionQuery ? reduceMotionQuery.matches : false);

    function normaliseMod(index, length) {
        if (! length) {
            return 0;
        }

        return ((index % length) + length) % length;
    }

    function determinePosition(offset, length) {
        if (offset === 0) {
            return 'active';
        }

        if (offset === 1) {
            return 'next';
        }

        if (length > 3 && offset === 2) {
            return 'far-next';
        }

        if (offset === length - 1) {
            return 'prev';
        }

        if (length > 3 && offset === length - 2) {
            return 'far-prev';
        }

        return 'hidden';
    }

    function applyPositions(state) {
        const { cards, current } = state;
        const length = cards.length;

        cards.forEach((card, index) => {
            const offset = normaliseMod(index - current, length);
            const position = determinePosition(offset, length);
            card.setAttribute('data-carousel-position', position);
        });
    }

    function setupCarousel(root) {
        if (! root || root.dataset.reviewCarouselReady === '1') {
            return;
        }

        const track = root.querySelector(SELECTORS.track);
        if (! track) {
            return;
        }

        const cards = Array.from(track.querySelectorAll('.wp-block-fitness-review-card'));
        if (! cards.length) {
            return;
        }

        cards.forEach((card, index) => {
            card.style.setProperty('--review-index', String(index));
        });

        track.style.setProperty('--review-count', String(cards.length));

        const state = {
            cards,
            current: 0,
            timer: null,
        };

        function goTo(index) {
            const nextIndex = normaliseMod(index, cards.length);

            if (nextIndex === state.current) {
                return;
            }

            state.current = nextIndex;
            applyPositions(state);
        }

        function step(amount) {
            goTo(state.current + amount);
        }

        function stop() {
            if (state.timer !== null) {
                window.clearInterval(state.timer);
                state.timer = null;
            }
        }

        function start() {
            if (prefersReducedMotion() || cards.length < 2) {
                return;
            }

            stop();
            state.timer = window.setInterval(() => {
                step(1);
            }, ROTATION_INTERVAL);
        }

        const prevButton = root.querySelector(SELECTORS.prev);
        const nextButton = root.querySelector(SELECTORS.next);

        if (cards.length <= 1) {
            if (prevButton) {
                prevButton.setAttribute('hidden', 'hidden');
            }

            if (nextButton) {
                nextButton.setAttribute('hidden', 'hidden');
            }
        } else {
            if (prevButton) {
                prevButton.removeAttribute('hidden');
            }

            if (nextButton) {
                nextButton.removeAttribute('hidden');
            }
        }

        if (prevButton) {
            prevButton.addEventListener('click', () => {
                step(-1);
                start();
            });
        }

        if (nextButton) {
            nextButton.addEventListener('click', () => {
                step(1);
                start();
            });
        }

        root.addEventListener('mouseenter', stop);
        root.addEventListener('mouseleave', start);
        root.addEventListener('focusin', stop);
        root.addEventListener('focusout', start);
        root.addEventListener('keydown', (event) => {
            if (event.defaultPrevented) {
                return;
            }

            if (event.key === 'ArrowUp' || event.key === 'PageUp') {
                event.preventDefault();
                step(-1);
                start();
            }

            if (event.key === 'ArrowDown' || event.key === 'PageDown') {
                event.preventDefault();
                step(1);
                start();
            }
        });

        if (reduceMotionQuery) {
            const handlePreferenceChange = () => {
                if (prefersReducedMotion()) {
                    stop();
                } else {
                    start();
                }
            };

            if (typeof reduceMotionQuery.addEventListener === 'function') {
                reduceMotionQuery.addEventListener('change', handlePreferenceChange);
            } else if (typeof reduceMotionQuery.addListener === 'function') {
                reduceMotionQuery.addListener(handlePreferenceChange);
            }
        }

        applyPositions(state);
        root.dataset.reviewCarouselReady = '1';

        if (cards.length > 1) {
            start();
        }
    }

    function initialiseAll() {
        document.querySelectorAll(SELECTORS.root).forEach(setupCarousel);
    }

    if (document.readyState !== 'loading') {
        initialiseAll();
    } else {
        document.addEventListener('DOMContentLoaded', initialiseAll);
    }

    if (window.wp && typeof window.wp.domReady === 'function') {
        window.wp.domReady(initialiseAll);
    }

    window.addEventListener('pageshow', initialiseAll, { once: true });
})();

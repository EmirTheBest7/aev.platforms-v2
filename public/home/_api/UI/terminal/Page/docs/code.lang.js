hljs.initHighlightingOnLoad();

const scrollView = document.querySelector('.Container .Contents');
const tabs = document.querySelectorAll('.Container .Contents .Content');
const nav = document.querySelectorAll('.Container nav a');
const currentNav = document.querySelector('.Container nav .current');
let currentTab = 0;

currentNav.style.width = `${nav[currentTab].clientWidth}px`;
scrollView.style.height = `${tabs[currentTab].clientHeight}px`;

nav.forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        const index = Array.prototype.indexOf.call(nav, e.target);
        if (currentTab === index) return;
        animateToTab(index);
    });
});

const animateToTab = index => {
    const el = tabs[index];
    const scrollStart = scrollView.scrollLeft;
    const startHeight = scrollView.clientHeight;
    nav[currentTab].classList.remove('active');
    nav[index].classList.add('active');

    const time = {
        start: performance.now(),
        duration: 700
    };


    const tick = now => {
        time.elapsed = now - time.start;
        const fadeOut = easeInOutCubic(time.elapsed, 1, -1, time.duration);
        const fadeIn = easeInOutCubic(time.elapsed, 0, 1, time.duration);
        const offset = easeInOutCubic(time.elapsed, scrollStart, el.offsetLeft - scrollStart, time.duration);
        const height = easeInOutCubic(time.elapsed, startHeight, el.clientHeight - startHeight, time.duration);

        const navOffset = easeInOutCubic(
            time.elapsed,
            nav[currentTab].offsetLeft,
            nav[index].offsetLeft - nav[currentTab].offsetLeft,
            time.duration);


        const indicatorWidth = easeInOutCubic(
            time.elapsed,
            nav[currentTab].clientWidth,
            nav[index].clientWidth - nav[currentTab].clientWidth,
            time.duration);


        currentNav.style.transform = `translateX(${navOffset}px)`;
        currentNav.style.width = `${indicatorWidth}px`;

        tabs[currentTab].style.opacity = fadeOut;
        tabs[index].style.opacity = fadeIn;
        scrollView.scrollLeft = offset;
        scrollView.style.height = `${height}px`;

        if (time.elapsed < time.duration) {
            requestAnimationFrame(tick);
        } else {
            currentTab = index;
        }
    };

    requestAnimationFrame(tick);
};

var easeInOutCubic = (t, b, c, d) => {
    if ((t /= d / 2) < 1) return c / 2 * t * t * t + b;
    return c / 2 * ((t -= 2) * t * t + 2) + b;
};
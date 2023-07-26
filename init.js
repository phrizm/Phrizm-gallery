document.addEventListener('DOMContentLoaded', function() {
    var swiperContainers = document.querySelectorAll('.swiper-container');

    swiperContainers.forEach(function(container) {
        var nextEl = container.querySelector('.swiper-button-next');
        var prevEl = container.querySelector('.swiper-button-prev');
        var paginationEl = container.querySelector('.swiper-pagination');
        var scrollbarEl = container.querySelector('.swiper-scrollbar');

        var swiper = new Swiper(container, {
            direction: 'horizontal',
            loop: true,
            pagination: {
                el: paginationEl,
            },
            navigation: {
                nextEl: nextEl,
                prevEl: prevEl,
            },
            scrollbar: {
                el: scrollbarEl,
            },
        });
    });
});

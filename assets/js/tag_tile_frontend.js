if(document.querySelector("#tag_tile").classList.contains("tag_tile_drop")) {
    const more = document.querySelector('.more_tags'),
        tiles = document.querySelector('.tag_tile_drop')

    more.addEventListener('click', function () {
        more.classList.toggle('active')
        tiles.classList.toggle('active')
    })
} else {
    jQuery('.slider').slick({
    infinite: true,
    slidesToShow: 3,
    slidesToScroll: 3
});
}
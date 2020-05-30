document.removedFst = 0;
$(document).ready(function () {
    loadImg();
    $(document).on('click', '.likeButton', doLike);
    $(document).on('dblclick', '.post-image', doLike);
    $(document).on('submit', '.commentContainer', function (e) {
        e.preventDefault();
        $.ajax({
            url: '/api/comment',
            dataType: 'json',
            data: $(this).serialize(),
            method: 'POST',
            success: function (data) {
                console.log(data);
                updatePosts();
            },
            error: function (err) {
                console.log(err);
            }
        });
    });
    $(document).on('click', '.followUser', function (e) {
        e.preventDefault();
        var iduser = $(this).data('usuario');
        $.ajax({
            url: '/api/followUser',
            dataType: 'JSON',
            method: 'POST',
            data: 'id_user=' + iduser,
            success: function (ret) {
                console.log(ret);
                if (ret.status) {
                    $('.followUser[data-usuario=' + iduser + ']').empty().append(ret.txt);
                }
            }
        });
    });

    function loadImg() {
        $('.post-image[data-img!=""]').each(function (index, elem) {
            $(elem).css('background-image', 'url(' + $(elem).data('img') + ')');
        });
    }

    function updatePosts() {
        $.ajax({
            url: '/api/getPosts',
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                var elems = '';
                data.forEach(element => {
                    elems += element.html;
                });
                $('#postContainer').html(elems);
                loadImg();

                if (!document.removedFst) {
                    $('.card-post')[$('.card-post').length - 1].remove();
                    document.removedFst = 1;
                }

            }
        });
    }

    function doLike() {
        var id = $(this).data('idpost');
        var likeBtn = $('.likeButton[data-idpost=' + id + ']');
        var likeBtn1 = likeBtn.children('svg');
        $.ajax({
            url: '/api/like',
            dataType: 'json',
            data: 'id_post=' + id,
            method: 'POST',
            success: function (data) {
                switch (data.status) {
                    case 'likeSuccess':
                        $(likeBtn).append("<i class='fas fa-heart text-danger'></i>");
                        $(likeBtn1[0]).remove();
                        break;
                    case 'deslikeSuccess':
                        $(likeBtn).append("<i class='far fa-heart'></i>");
                        $(likeBtn1[0]).remove();
                        break;
                }

            }
        });
    }
});
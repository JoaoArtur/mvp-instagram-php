$(document).ready(function() {
    $('.formLogin').on('submit', function(event) {
        event.preventDefault();

        $.ajax({
            url: '/api/login',
            data: $(this).serialize(),
            dataType: 'json',
            method: 'post',
            success: function(data) {
                if (data.status) {
                    window.location.href = '/';
                } else {
                    $('#msg').removeClass('box-hidden');
                    $('#msg.box-content').append('Usuário e/ou senha incorretos.');
                }
            }

        });
    });
    $('.formRegister').on('submit', function(event) {
        event.preventDefault();
        $.ajax({
            url: '/api/register',
            data: $(this).serialize(),
            dataType: 'json',
            method: 'post',
            success: function(data) {
                if (data.status) {
                    window.location.href = '/';
                } else {
                    $('#msg').removeClass('box-hidden');
                    $('#msg > .box-content').append('Erro ao criar conta, tente novamente.');
                }
            }

        });
    });
});
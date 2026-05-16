(function($) {
    $(document).ready(function() {
        // 监听选项的变化事件
        $('input[name="Micnt_Index"]').on('change', function() {
            var value = $(this).val();
            $('.mi-adminst').hide();
            $('#custom-div-' + value).show();
        });
        
        // 初始化时根据选项的值来显示或隐藏自定义元素
        var value = $('input[name="Micnt_Index"]:checked').val();
        $('.mi-adminst').hide();
        $('#custom-div-' + value).show();
    });
})(jQuery);

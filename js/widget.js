// 各种小控件

(function ($) {
    
    // 句子编辑和发表框
    $.fn.sentencePoster = function () {
        var form = $(this);
        
        // 自适应的文本框
        var ta = form.find('textarea').keyup(function () {
            if (ta.scrollTop() !== 0) {
                ta.animate({height:ta.get(0).scrollHeight+'px'}, 'fast');
            }
        });
        
        // 点击完发布按钮后，禁用自身
        var okBtn = form.find('input[type=submit]').click(function () {
            ta.addClass('disabled');
            okBtn.animate({height: 'toggle'}, 'fast');
        });
        
        // 在表单提交期间禁用提交按钮
        form.submit(function () {
            okBtn.prop('disabled', true);
        });

        // 尽量减少发表框的大小和影响
        // chrome 有bug
        ta.bind('keyup', function () {
            okBtn.prop('disabled', ta.val() == '');
        });
        
        return form;
    };
})(jQuery);
/* 
 * by xc
 */

$(function () {
    
    // 单击加书签
    var marking = 'marking';
    var lis = $('li.sentence').click(function () {
        var that = $(this);
        if (that.hasClass(marking)) {
            lis.removeClass(marking);
        } else {
            lis.removeClass(marking)
            that.addClass(marking);
        }
    });
    
    // 单击喜欢
    $('.like.btn').click(function () {
        var that = $(this);
        if (that.hasClass('done')) return;
        that.removeClass('tobe').addClass('done');
        
        var li = that.parents('li');
        $.post(_G.ROOT + 'sentence/' + li.attr('data-id'), {a: 'like'}, function () {
            
            // +1 TODO animation
            var numBigSpan = li.find('.like-num').show('fast');
            var numSpan = numBigSpan.find('.num');
            var num = +numSpan.text() + 1;
            num = +numSpan.text();
            numSpan.digitAnimation(num, num+1);
            
        });
    });
    
    // 单击显示喜欢的人
    var expended = 'expended';
    $('.like-num.btn').each(function () {
        var that = $(this);
        if (that.find('.num').text() == '0') {
            that.addClass(expended).addClass('disabled');
        }
    }).click(function () {
        var that = $(this);
        if (that.find('.num').text() == '0') {
            return;
        }
        var expendedData = that.data(expended);
        var li = that.parents('li');
        var div = li.find('div.who.like');
        if (!expendedData) {
            that.addClass(expended);
            
            // ajax 取得喜欢的人
            div.show();
            var loading = div.find('.loading').show();
            $.get(_G.ROOT + 'sentence/' + li.attr('data-id'), {a:'getLikeUsers'}, function (ret) {
                div.find('ul').html(ret);
                loading.hide();
            }, 'html');
        } else {
            div.animate({height: 'hide'}, 'fast');
        }
        if (expendedData) that.removeClass(expended);
        that.data(expended, !expendedData);
    });
    
    // 编辑自己的句子
    $('li.sentence .edit.btn').click(function () {
        var that = $(this).hide();
        var li = that.parents('li');
        var text = li.find('.text');
        var id = li.attr('data-id');
        var html = text.html();
        var form = $('<form action="' + _G.ROOT + 'sentence/' + '" method="post">' +
            '<textarea name="text">' + text.text() + '</textarea>' +
            '<input type="submit" value="好了"></input>' + 
            '<span class="cancel btn">取消</span>' +
            '<span class="del btn">删除这个句子</span>' +
            '</form>').
            sentencePoster();
        var ta = form.find('textarea');
        var url = _G.ROOT + 'sentence/' + id;
        form.submit(function () {
            var textStr = ta.val();
            $.post(url, {
                a: 'edit',
                text: textStr
            }, function () {
                text.html(textStr.replace(/(?!>)\n/g, "<br />\n"));
                that.show();
            }, 'html');
            return false;
        });
        form.find('.del.btn').click(function () {
            $.post(url, {a:'del'}, function () {
                li.animate({height:'hide'}, 'fast');
            });
        });
        form.find('.cancel.btn').click(function () {
            text.html(html);
            that.show();
        });
        text.html(form);
        ta.focus().stop();
        ta.animate({height:ta.get(0).scrollHeight + 'px'}, 'fast');
    });
});
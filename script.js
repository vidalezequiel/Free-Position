jQuery(function($){
  $('.efp-draggable').each(function(){
    let el = $(this);
    let id = el.data('id');
    let postId = el.data('post');

    el.css('position', 'absolute');

    const toolbar = $('<div class="efp-toolbar" style="display:none;"><button class="reset">↺</button></div>');
    $('body').append(toolbar);

    el.on('mousedown', function(e){
      let dx = e.pageX - el.offset().left;
      let dy = e.pageY - el.offset().top;

      toolbar.css({
        top: el.offset().top - 30,
        left: el.offset().left,
        display: 'flex'
      });

      $(document).on('mousemove.efp', function(e){
        let left = e.pageX - dx;
        let top = e.pageY - dy;
        el.css({ left, top });
        toolbar.css({ top: top - 30, left });
      });

      $(document).on('mouseup.efp', function(){
        $(document).off('.efp');

        let top = parseInt(el.css('top'));
        let left = parseInt(el.css('left'));
        $.post(efp_ajax_obj.ajaxurl, {
          action: 'efp_save_position',
          nonce: efp_ajax_obj.nonce,
          post_id: postId,
          widget_id: id,
          position: { top, left }
        });
      });
    });

    toolbar.find('.reset').on('click', function(){
      el.css({ top: '', left: '' });
      toolbar.hide();
    });
  });
});

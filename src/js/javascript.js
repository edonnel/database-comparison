$(function() {

    $('.indexes-table tbody tr').each(function(index, element) {
        const $row = $('.indexes-table tbody tr:nth-child('+index+')');
        let height = 0;

        $($row).each(function(index, element) {
            const height_ = $(element).height();

            if (height_ > height)
                height = height_;
        });

        $row.css('height', height);
    });

});
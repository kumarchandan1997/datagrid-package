function resetForm() {
    document.querySelector('input[name="search"]').value = '';
    document.querySelector('form').submit();
}
(function ($) {
    var CheckboxDropdown = function (el) {
        var _this = this;
        this.isOpen = false;
        this.areAllChecked = false;
        this.$el = $(el);
        this.$label = this.$el.find('.dropdown-label');
        this.$checkAll = this.$el.find('[data-toggle="check-all"]').first();
        this.$inputs = this.$el.find('[type="checkbox"]');

        this.onCheckBox();

        this.$label.on('click', function (e) {
            e.preventDefault();
            _this.toggleOpen();
        });

        this.$checkAll.on('click', function (e) {
            e.preventDefault();
            _this.onCheckAll();
        });

        this.$inputs.on('change', function (e) {
            _this.onCheckBox();
        });
    };

    CheckboxDropdown.prototype.onCheckBox = function () {
        this.updateStatus();
    };

    CheckboxDropdown.prototype.updateStatus = function () {
        var checkedCount = this.$el.find(':checked').not('[value="id"]').length;
        var totalCount = this.$inputs.not('[value="id"]').length;

        if (checkedCount === totalCount && checkedCount !== 0) {
            this.$checkAll.html('Uncheck All');
            this.areAllChecked = true;
        } else {
            this.$checkAll.html('Check All');
            this.areAllChecked = false;
        }
    };

    CheckboxDropdown.prototype.onCheckAll = function () {
        if (!this.areAllChecked) {
            this.$checkAll.html('Uncheck All');
            this.$inputs.not('[value="id"]').prop('checked', true);
            this.areAllChecked = true;
        } else {
            this.$checkAll.html('Check All');
            this.$inputs.not('[value="id"]').prop('checked', false);
            this.areAllChecked = false;
        }
    };

    CheckboxDropdown.prototype.toggleOpen = function (forceOpen) {
        var _this = this;

        if (!this.isOpen || forceOpen) {
            this.isOpen = true;
            this.$el.addClass('on');
            $(document).on('click', function (e) {
                if (!$(e.target).closest('[data-control]').length) {
                    _this.toggleOpen();
                }
            });
        } else {
            this.isOpen = false;
            this.$el.removeClass('on');
            $(document).off('click');
        }
    };

    var checkboxesDropdowns = document.querySelectorAll('[data-control="checkbox-dropdown"]');
    for (var i = 0, length = checkboxesDropdowns.length; i < length; i++) {
        var dropdown = new CheckboxDropdown(checkboxesDropdowns[i]);
    }
})(jQuery);


$(document).ready(function () {
    $('select[name="rows-per-page"]').change(function () {
        var rowsPerPage = $(this).val();
        var currentUrl = window.location.href;
        if (currentUrl.indexOf('?') !== -1) {
            if (currentUrl.indexOf('rows-per-page=') !== -1) {
                var regExp = new RegExp('rows-per-page=\\d+');
                currentUrl = currentUrl.replace(regExp, 'rows-per-page=' + rowsPerPage);
            } else {
                currentUrl += '&rows-per-page=' + rowsPerPage;
            }
        } else {
            currentUrl += '?rows-per-page=' + rowsPerPage;
        }
        window.location.href = currentUrl;
    });
    $('#search-btn').click(function (event) {
        event.preventDefault();

        var url = new URL(window.location.href);
        var searchParams = new URLSearchParams(url.search);
        var searchValue = searchParams.get('search');

        if (searchValue !== null) {
            searchParams.set('search', searchValue);
        } else {
            searchParams.append('search', $('#search').val());
        }

        url.search = searchParams.toString();
        var finalUrl = url.toString();

        if ($('#search').val() == '') {
            $('.error').text('This field is required to search');
        } else {
            window.location.href = finalUrl;
        }
    });
});

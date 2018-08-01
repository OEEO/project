define(['jquery'], function ($) {
    var Collapse = function (option) {
        var defaultOption = {
            data: [],
            defaultTitle: '',
            target: '',
            onchange: function () {}
        };

        this.options = $.extend(defaultOption, option);

        this.active = false;

        this.$target = $(this.options.target);

        this.onchange = this.options.onchange;

        this.init();
        this.initEvent();
    };

    function appendItemList(data) {
        var fragment = document.createDocumentFragment();
        for (var i = 0, num = data.length; i < num; i++) {
            var item = document.createElement('li');
            item.className = 'collapse-item';
            item.innerText = data[i].label;
            item.setAttribute('data-collapse-val', data[i].value);
            fragment.appendChild(item);
        }
        return fragment;
    }

    Collapse.prototype.init = function () {
        this.$target.addClass('collapse');

        var fragment = document.createDocumentFragment();

        var collapseHeader = document.createElement('div');
        collapseHeader.className = 'collapse-header';
        collapseHeader.innerHTML = '<span>' + this.options.defaultTitle + '</span><i class="caret"></i>';
        fragment.appendChild(collapseHeader);

        var collapseContainer = document.createElement('div');
        collapseContainer.className = 'collapse-container';
        var collpaseList = document.createElement('ul');
        collpaseList.className = 'collapse-list';

        var collapseItemList = appendItemList(this.options.data);
        collpaseList.appendChild(collapseItemList);
        collapseContainer.appendChild(collpaseList);
        fragment.appendChild(collapseContainer);

        this.$target[0].appendChild(fragment);
    };

    Collapse.prototype.initEvent = function () {
        var self = this;
        this.$target.on('click', '.collapse-header', function (e) {
            e.stopPropagation();
            self.active = !self.active;

            if (self.active) {
                self.$target.addClass('active');
            } else {
                self.$target.removeClass('active');
            }

            var tag = $(this).siblings('.collapse-container');
            var $this = $(this);

            $(document).on('click', function (e) {
                e.stopPropagation();
                if ($(e.target).closest(tag).length === 0 && self.active) {
                    self.active = false;
                    self.$target.removeClass('active');
                }
            });
        });

        this.$target.on('click', '.collapse-item', function () {
            $(this).siblings('li.active').removeClass('active');
            $(this).addClass('active');
            var val = $(this).data('collapse-val') + '';
            self.onchange(val);

            for (var i = 0, num = self.options.data.length; i < num; i++) {
                if (self.options.data[i].value === val) {
                    $('.collapse-header span').text(self.options.data[i].label);
                    break;
                }
            }

            self.active = false;
            self.$target.removeClass('active');
        });
    };

    return Collapse;
});
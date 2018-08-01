/**
 * 这是入口文件
 */
require(['../common'], function (common) {
    require(['jquery', 'utils', 'datePicker', 'url', 'collapse', 'extend/schedule/time-slider'], function ($, utils, DatePicker, URL, Collapse, timeSlider) {
        $(document).ready(function () {
            var datePicker = new DatePicker({
                target: '#date-picker',
                activeDate: [1, 2, 3],
                onchangeMonth: function (date) {
                    var url = URL.GET_ACTIVE_DATE;
                    url = url.replace('{game}', 'kog');
                    url = url.replace('{e_index}', '1');
                    url = url.replace('{/type_id}', '');
                    url = url.replace('{date}', date);
                    
                    utils.getScript(url, 'date', { timeout: 10 }, function (err, data) {
                        if (err || !window.date) {
                            console.error(err);
                        } else {
                            datePicker.changeActiveDate(data);
                        }
                    });
                },

                onchange: function (date) {
                    console.log(date);
                }
            });

            var collapse = new Collapse({
                target: '#collapse',
                data: [
                    { label: '1', value: '1' },
                    { label: '2', value: '2' }
                ],
                defaultTitle: 'collapse'
            });
        });
    });
});
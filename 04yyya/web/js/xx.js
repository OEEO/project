/**
 * Created by toreant on 2017/9/19.
 */
var xxObject = {
    onload: function () {
        var punch = storage.get('punch');
        alert(JSON.stringify(punch));
        $('.page_xx .main').html(JSON.stringify(punch));
    }
};
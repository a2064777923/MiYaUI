//问答搜索框判断
document.addEventListener('DOMContentLoaded', function() {
  var searchButton = document.querySelector('.ask-search-form button');
  searchButton.addEventListener('click', function() {
    var input = document.querySelector('.ask-search-form input');
    var searchValue = input.value.trim();
    if (searchValue === '') {
        Qmsg['success']('搜索框不能为空！', {
            html: true
        });
    }
  });
});
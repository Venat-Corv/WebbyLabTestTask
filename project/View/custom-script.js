document.getElementById('addStar').addEventListener('click', function () {
    var starsContainer = document.getElementById('stars');
    var inputGroup = document.createElement('div');
    inputGroup.className = 'star-input-group mb-2';
    inputGroup.innerHTML = '<input type="text" class="form-control" name="stars[]" required>' +
        '<button type="button" class="btn btn-danger btn-remove-star">Видалити</button>';
    starsContainer.appendChild(inputGroup);
});

document.addEventListener('click', function (event) {
    if (event.target.classList.contains('btn-remove-star')) {
        event.target.parentElement.remove();
    }
});

document.querySelector('form').addEventListener('submit', function(event) {
    var stars = document.querySelectorAll('input[name="stars[]"]');
    var actorsNotEmpty = false;
    stars.forEach(function(star) {
        if (star.value.trim() !== '') {
            actorsNotEmpty = true;
        }
    });
    if (!starsNotEmpty) {
        alert('Потрібно ввести хоча б одного актора!');
        event.preventDefault();
    }
});
function loadDetails() {
    var box = document.getElementById("details");

    if (!box) {
        return;
    }

    var params = new URLSearchParams(window.location.search);
    var id = params.get("id");

    fetch("part2.php?action=product&id=" + id)
        .then(function(response) {
            return response.json();
        })
        .then(function(product) {
            if (!product.product_id) {
                box.innerHTML = "<p>Product not found.</p>";
                return;
            }

            box.innerHTML =
                "<div class='box'>" +
                "<img src='" + product.image + "' alt='Product'>" +
                "<h2>" + product.product_name + "</h2>" +
                "<p>Category: " + product.category + "</p>" +
                "<h3>₹" + product.price + "</h3>" +
                "<p>" + product.description + "</p>" +
                "<button onclick='addProduct(" + product.product_id + ")'>Add to Cart</button>" +
                "</div>";
        })
        .catch(function() {
            box.innerHTML = "<p>Unable to load product.</p>";
        });
}

function addProduct(id) {
    var data = new FormData();
    data.append("product_id", id);

    fetch("part1.php?action=add", {
        method: "POST",
        body: data
    })
    .then(function(response) {
        return response.text();
    })
    .then(function(message) {
        alert(message);
    });
}

function loadSummary() {
    var box = document.getElementById("summary");

    if (!box) {
        return;
    }

    fetch("part2.php?action=summary")
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            box.innerHTML = "";

            if (data.items.length == 0) {
                box.innerHTML = "<p>Your cart is empty.</p>";
                return;
            }

            for (var i = 0; i < data.items.length; i++) {
                box.innerHTML +=
                    "<div class='box'>" +
                    "<b>" + data.items[i].product_name + "</b>" +
                    "<p>Quantity: " + data.items[i].quantity + "</p>" +
                    "<p>Amount: ₹" + data.items[i].amount + "</p>" +
                    "</div>";
            }

            box.innerHTML += "<h3>Total: ₹" + data.total + "</h3>";
        })
        .catch(function() {
            box.innerHTML = "<p>Unable to load summary.</p>";
        });
}

loadDetails();
loadSummary();

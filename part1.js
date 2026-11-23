function loadProducts() {
    var box = document.getElementById("products");

    if (!box) {
        return;
    }

    fetch("part1.php?action=products")
        .then(function(response) {
            return response.json();
        })
        .then(function(products) {
            box.innerHTML = "";

            if (products.length == 0) {
                box.innerHTML = "<p>No products found.</p>";
                return;
            }

            for (var i = 0; i < products.length; i++) {
                box.innerHTML +=
                    "<div class='card'>" +
                    "<a href='details_part2.html?id=" + products[i].product_id + "'>" +
                    "<img src='" + products[i].image + "' alt='Product'>" +
                    "<h3>" + products[i].product_name + "</h3></a>" +
                    "<p>₹" + products[i].price + "</p>" +
                    "<p>" + products[i].category + "</p>" +
                    "<button onclick='addToCart(" + products[i].product_id + ")'>Add to Cart</button>" +
                    "</div>";
            }
        })
        .catch(function() {
            box.innerHTML = "<p>Unable to load products. Check Apache, MySQL and database.</p>";
        });
}

function addToCart(id) {
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
    })
    .catch(function() {
        alert("Unable to add product to cart.");
    });
}

function loadCart() {
    var box = document.getElementById("cart");

    if (!box) {
        return;
    }

    fetch("part1.php?action=cart")
        .then(function(response) {
            return response.json();
        })
        .then(function(items) {
            box.innerHTML = "";
            var total = 0;

            if (items.length == 0) {
                box.innerHTML = "<p>Your cart is empty.</p>";
                document.getElementById("cartTotal").innerHTML = "";
                return;
            }

            for (var i = 0; i < items.length; i++) {
                var amount = items[i].price * items[i].quantity;
                total += amount;

                box.innerHTML +=
                    "<div class='cartItem'>" +
                    "<b>" + items[i].product_name + "</b>" +
                    "<p>Price: ₹" + items[i].price + "</p>" +
                    "<p>Quantity: " + items[i].quantity + "</p>" +
                    "<p>Amount: ₹" + amount + "</p>" +
                    "<button onclick='removeItem(" + items[i].cart_id + ")'>Remove</button>" +
                    "</div>";
            }

            document.getElementById("cartTotal").innerHTML =
                "<h3>Total: ₹" + total + "</h3>";
        })
        .catch(function() {
            box.innerHTML = "<p>Unable to load cart.</p>";
        });
}

function removeItem(id) {
    var data = new FormData();
    data.append("cart_id", id);

    fetch("part1.php?action=remove", {
        method: "POST",
        body: data
    })
    .then(function() {
        loadCart();
    });
}

loadProducts();
loadCart();

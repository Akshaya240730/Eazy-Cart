function loadCheckoutTotal() {
    var box = document.getElementById("checkoutTotal");

    if (!box) {
        return;
    }

    fetch("part3.php?action=total")
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            box.innerHTML = "<h3>Order Total: ₹" + data.total + "</h3>";
        });
}

function placeOrder() {
    var form = document.getElementById("checkoutForm");
    var message = document.getElementById("message");
    var data = new FormData(form);

    fetch("part3.php?action=place", {
        method: "POST",
        body: data
    })
    .then(function(response) {
        return response.text();
    })
    .then(function(result) {
        if (result == "Cart is empty" || result == "Order could not be placed") {
            message.innerHTML = result;
        } else {
            message.innerHTML = "Order placed successfully. Order No: " + result;
            setTimeout(function() {
                window.location.href = "tracking_part3.html";
            }, 1000);
        }
    })
    .catch(function() {
        message.innerHTML = "Unable to place order.";
    });
}

function loadTracking() {
    var box = document.getElementById("tracking");

    if (!box) {
        return;
    }

    fetch("part3.php?action=tracking")
        .then(function(response) {
            return response.json();
        })
        .then(function(order) {
            if (!order.order_number) {
                box.innerHTML = "<p>No order found.</p>";
                return;
            }

            box.innerHTML =
                "<div class='box'>" +
                "<p><b>Order Number:</b> " + order.order_number + "</p>" +
                "<p><b>Name:</b> " + order.customer_name + "</p>" +
                "<p><b>Total:</b> ₹" + order.total_amount + "</p>" +
                "</div>" +
                "<div class='step'>1. Order Placed ✓</div>" +
                "<div class='step'>2. Processing</div>" +
                "<div class='step'>3. Shipped</div>" +
                "<div class='step'>4. Delivered</div>";
        })
        .catch(function() {
            box.innerHTML = "<p>Unable to load tracking.</p>";
        });
}

document.getElementById("checkoutForm")?.addEventListener("submit", function(event) {
    event.preventDefault();
    placeOrder();
});

loadCheckoutTotal();
loadTracking();

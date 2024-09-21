<?php
// Вывод ошибок
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Подключение к бд
$host = 'localhost';
$login = 'root';
$pass = 'root';
$name = 'cookie_cart';

$pdo = new PDO("mysql:host=$host;dbname=$name", $login, $pass);

try {
  $products = $pdo
    ->query('SELECT * FROM `products`')
    ->fetchAll();
} catch (PDOException $e) {
  return;
}

// Добавляет товар
if (isset($_POST['addToCart'])) {
  $cartProducts = [];
  if (isset($_COOKIE['cart'])) {
    $cartProducts = explode(',', $_COOKIE['cart']);
  }
  if (!in_array($_POST['id'], $cartProducts)) {
    setcookie('cart', implode(',', array_merge($cartProducts, [$_POST['id']])), time() + 3600);
  }
  echo '<script>location.reload()</script>';
}

// Удаляем товар
if (isset($_POST['removeFromCart'])) {
  $cartProducts = [];
  if (isset($_COOKIE['cart'])) {
    $cartProducts = explode(',', $_COOKIE['cart']);
  }
  if (in_array($_POST['id'], $cartProducts)) {
    setcookie('cart', implode(',', array_diff($cartProducts, [$_POST['id']])), time() + 3600);
  }
  echo '<script>location.reload()</script>';
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cookie cart</title>
</head>

<body>
  <!-- Корзина -->
  <section class="container catalog">
    <h2>Корзина</h2>
    <?php if (isset($_COOKIE['cart'])): ?>
      <div class="wrapper">
        <?php foreach (explode(',', $_COOKIE['cart']) as $cartProductId): ?>
          <div class="card">
            <?php
            // Достаём информацию о товаре
            try {
              $stmt = $pdo->prepare('SELECT * FROM `products` WHERE `id` = ?');
              $stmt->execute([$cartProductId]);
              $cartProduct = $stmt->fetch();
            } catch (PDOException $e) {
              return;
            }
            ?>
            <h1><?= $cartProduct['name'] ?></h1>
            <p><?= $cartProduct['price'] ?> $</p>
            <form method="post">
              <input type="hidden" name="id" value="<?= $cartProduct['id'] ?>">
              <button class="button" type="submit" name='removeFromCart'>
                Удалить из корзины
              </button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else:  ?>
      <h1>Нет продуктов в корзине</h1>
    <?php endif;  ?>
  </section>

  <!-- Каталог -->
  <section class="container catalog">
    <h2>Каталог</h2>
    <div class="wrapper">
      <?php foreach ($products as $product): ?>
        <div class="card">
          <h1><?= $product['name'] ?></h1>
          <p><?= $product['price'] ?> $</p>
          <form method="post">
            <input type="hidden" name="id" value="<?= $product['id'] ?>">
            <button class="button" type="submit" name='addToCart'>
              Добавить в корзину
            </button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <style>
    body {
      background: #fbfbfb;
      font-family: "Arial";
    }

    .catalog {
      padding-top: 1rem;
    }

    .container {
      width: clamp(320px, 100%, 1300px);
      margin-inline: auto;
    }

    .wrapper {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      column-gap: 1rem;
      row-gap: 1rem;
    }

    .card {
      padding: 0.5rem;
      border-radius: 1rem;
      background: #fff;
    }

    .button {
      width: 100%;
      border-radius: 1rem;
      padding: 0.5rem;
      background: blue;
      color: white;
      border: none;
    }
  </style>
</body>

</html>
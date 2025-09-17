<?php
require 'databases/config.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM articles");
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    include 'article_view.html';
} catch (PDOException $e) {
    echo $e->getMessage();
}

?>

<script>
    let result = <?php echo json_encode($result); ?>;
</script>
<script src="js/main.js"></script>

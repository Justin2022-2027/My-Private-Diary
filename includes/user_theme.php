<?php
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['user_id'])) return;
require_once __DIR__ . '/../db_connect.php';
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT theme_name, font_choice, background_color, text_color FROM themes WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$conn->close();
$theme_name = $theme['theme_name'] ?? 'default';
$font_choice = $theme['font_choice'] ?? 'Inter';
$background_color = $theme['background_color'] ?? '#ffffff';
$text_color = $theme['text_color'] ?? '#1e293b';
$theme_palettes = [
    'default' => ['#ff9fb0', '#1e293b'],
    'ocean' => ['#0ea5e9', '#082f49'],
    'forest' => ['#22c55e', '#14532d'],
    'sunset' => ['#f97316', '#431407'],
    'lavender' => ['#a855f7', '#3b0764']
];
$primary = $theme_palettes[$theme_name][0] ?? '#ff9fb0';
$primary_dark = $theme_palettes[$theme_name][1] ?? '#1e293b';
?>
<style>
:root {
    --primary: <?php echo $primary; ?>;
    --primary-dark: <?php echo $primary_dark; ?>;
    --bg-color: <?php echo $background_color; ?>;
    --text-color: <?php echo $text_color; ?>;
    --font-family: '<?php echo $font_choice; ?>', sans-serif;
}
body {
    background-color: var(--bg-color) !important;
    color: var(--text-color) !important;
    font-family: var(--font-family) !important;
}
</style> 
<?php
require_once 'db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $category = $_POST['category'] ?? 'أخرى';
    $expense_date = $_POST['expense_date'] ?? '';

    $allowed_categories = ['طعام ومشروبات', 'مواصلات', 'فواتير وخدمات', 'أخرى'];

    if ($title === '' || mb_strlen($title) < 2) {
        $error = 'يرجى إدخال بيان واضح للمصروف (حرفان على الأقل).';
    } elseif ($amount === false || $amount <= 0) {
        $error = 'يرجى إدخال مبلغ صحيح أكبر من الصفر.';
    } elseif (!in_array($category, $allowed_categories, true)) {
        $error = 'يرجى اختيار فئة صحيحة.';
    } elseif ($expense_date === '') {
        $error = 'يرجى تحديد تاريخ المصروف.';
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO expenses (title, amount, category, expense_date) VALUES (:title, :amount, :category, :expense_date)"
        );
        $stmt->execute([
            ':title'        => $title,
            ':amount'       => round($amount, 2),
            ':category'     => $category,
            ':expense_date' => $expense_date
        ]);
        $message = 'تمت إضافة المصروف بنجاح إلى قاعدة البيانات.';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تتبع المصاريف - إضافة مصروف</title>
    <style>
        * { box-sizing: border-box; font-family: Tahoma, Arial, sans-serif; }
        body {
            margin: 0;
            background: #f4f6f9;
            color: #1f2937;
            padding: 30px 15px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.06);
        }
        h1 {
            margin-top: 0;
            font-size: 22px;
            color: #111827;
            margin-bottom: 20px;
        }
        .form-group { margin-bottom: 16px; }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            font-size: 14px;
        }
        input[type="text"], input[type="number"], input[type="date"], select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 15px;
        }
        .btn {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 11px 20px;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
        }
        .btn:hover { background: #1d4ed8; }
        .link-btn {
            display: inline-block;
            margin-top: 18px;
            color: #2563eb;
            text-decoration: none;
            font-weight: bold;
        }
        .alert-success {
            background: #dcfce7;
            color: #166534;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>إضافة مصروف جديد</h1>

    <?php if ($message !== ''): ?>
        <div class="alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div id="js-error" class="alert-error" style="display:none;"></div>

    <form id="expenseForm" method="POST" action="">
        <div class="form-group">
            <label for="title">بيان المصروف *</label>
            <input type="text" id="title" name="title" maxlength="255" placeholder="مثال: اشتراك إنترنت أو وجبة غداء" required>
        </div>

        <div class="form-group">
            <label for="amount">المبلغ *</label>
            <input type="number" id="amount" name="amount" step="0.01" min="0.01" placeholder="مثال: 150.00" required>
        </div>

        <div class="form-group">
            <label for="category">الفئة *</label>
            <select id="category" name="category">
                <option value="طعام ومشروبات">طعام ومشروبات</option>
                <option value="مواصلات">مواصلات</option>
                <option value="فواتير وخدمات">فواتير وخدمات</option>
                <option value="أخرى" selected>أخرى</option>
            </select>
        </div>

        <div class="form-group">
            <label for="expense_date">التاريخ *</label>
            <input type="date" id="expense_date" name="expense_date" required>
        </div>

        <button type="submit" class="btn">حفظ المصروف</button>
    </form>

    <a href="expenses.php" class="link-btn">عرض سجل المصاريف والمجموع ←</a>
</div>

<script>
    const form = document.getElementById('expenseForm');
    const titleInput = document.getElementById('title');
    const amountInput = document.getElementById('amount');
    const jsError = document.getElementById('js-error');

    form.addEventListener('submit', (e) => {
        const titleVal = titleInput.value.trim();
        const amountVal = parseFloat(amountInput.value);

        if (titleVal.length < 2) {
            e.preventDefault();
            jsError.textContent = 'يرجى إدخال بيان واضح للمصروف (حرفان على الأقل).';
            jsError.style.display = 'block';
        } else if (isNaN(amountVal) || amountVal <= 0) {
            e.preventDefault();
            jsError.textContent = 'يرجى إدخال مبلغ صحيح أكبر من الصفر.';
            jsError.style.display = 'block';
        } else {
            jsError.style.display = 'none';
        }
    });
</script>
</body>
</html>
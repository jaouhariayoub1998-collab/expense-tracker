<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($action === 'delete' && $id) {
        $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
    header("Location: expenses.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM expenses ORDER BY expense_date DESC, id DESC");
$expenses = $stmt->fetchAll();

$totalStmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) AS total FROM expenses");
$totalAmount = (float)($totalStmt->fetch()['total'] ?? 0);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سجل المصاريف والمجموع</title>
    <style>
        * { box-sizing: border-box; font-family: Tahoma, Arial, sans-serif; }
        body {
            margin: 0;
            background: #f4f6f9;
            color: #1f2937;
            padding: 30px 15px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.06);
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }
        h1 { margin: 0; font-size: 22px; color: #111827; }
        .btn-add {
            background: #2563eb;
            color: #fff;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
        }
        .btn-add:hover { background: #1d4ed8; }
        .summary-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e3a8a;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 16px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px 10px;
            border-bottom: 1px solid #e5e7eb;
            text-align: right;
            font-size: 14px;
        }
        th { background: #f9fafb; font-weight: bold; }
        .badge {
            background: #f3f4f6;
            color: #374151;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: bold;
        }
        .btn-delete {
            border: none;
            background: #fee2e2;
            color: #991b1b;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
        }
        .empty {
            text-align: center;
            padding: 30px;
            color: #6b7280;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="top-bar">
        <h1>سجل المصاريف والميزانية</h1>
        <a href="index.php" class="btn-add">+ إضافة مصروف جديد</a>
    </div>

    <div class="summary-box">
        إجمالي المصاريف المسجلة: <?= number_format($totalAmount, 2) ?>
    </div>

    <?php if (count($expenses) === 0): ?>
        <div class="empty">لا توجد مصاريف مسجلة حالياً. أضف أول مصروف للبدء.</div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>البيان</th>
                        <th>المبلغ</th>
                        <th>الفئة</th>
                        <th>التاريخ</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expenses as $expense): ?>
                        <tr>
                            <td><?= (int)$expense['id'] ?></td>
                            <td><?= htmlspecialchars($expense['title']) ?></td>
                            <td><strong><?= number_format((float)$expense['amount'], 2) ?></strong></td>
                            <td><span class="badge"><?= htmlspecialchars($expense['category']) ?></span></td>
                            <td><?= htmlspecialchars($expense['expense_date']) ?></td>
                            <td>
                                <form method="POST" action="" onsubmit="return confirm('هل أنت متأكد من حذف هذا المصروف؟');">
                                    <input type="hidden" name="id" value="<?= (int)$expense['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn-delete">حذف</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
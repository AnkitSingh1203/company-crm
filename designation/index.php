<?php

$page_title = "Designations";

include "../components/header.php";
include "../includes/database.php";

$search = trim($_GET['search'] ?? '');

$sql = "
    SELECT
        d.id,
        d.designation_name,
        d.status,
        d.created_at,
        dep.department_name
    FROM designations d
    LEFT JOIN departments dep
        ON d.department_id = dep.id
";

$countSql = "
    SELECT COUNT(*)
    FROM designations d
    LEFT JOIN departments dep
        ON d.department_id = dep.id
";

$where = "";
$params = [];

if ($search !== '') {

    $where = "
        WHERE d.designation_name LIKE ?
        OR dep.department_name LIKE ?
    ";

    $keyword = "%{$search}%";

    $params = [
        $keyword,
        $keyword
    ];
}

$sql .= $where;
$countSql .= $where;

$limit = 10;

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);

$totalRecords = $countStmt->fetchColumn();

$totalPages = ceil($totalRecords / $limit);

$offset = ($page - 1) * $limit;

$sql .= " ORDER BY d.id DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$designations = $stmt->fetchAll();

?>

<div class="dashboard">

    <?php include "../components/sidebar.php"; ?>

    <div class="main">

        <?php include "../components/navbar.php"; ?>

        <div class="content">

            <div class="page-header">

                <h1>Designations</h1>

                <a href="add.php" class="add-btn">

                    <i class="fa-solid fa-plus"></i>

                    Add Designation

                </a>

            </div>

            <div class="search-bar">

                <form method="GET">

                    <input
                        type="text"
                        name="search"
                        placeholder="Search designation or department..."
                        value="<?= htmlspecialchars($search) ?>"
                    >

                    <input
                        type="hidden"
                        name="page"
                        value="1"
                    >

                    <button type="submit">

                        <i class="fa fa-search"></i>

                    </button>

                </form>

            </div>

            <div class="table-container">

                <table class="employee-table">

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Designation Name</th>

                            <th>Department</th>

                            <th>Status</th>

                            <th>Created At</th>

                            <th>Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php if (count($designations) > 0): ?>

                            <?php foreach ($designations as $designation): ?>

                                <tr>

                                    <td>
                                        <?= htmlspecialchars($designation['id']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($designation['designation_name']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $designation['department_name'] ?? 'N/A'
                                        ) ?>
                                    </td>

                                    <td>

                                        <span class="status <?= $designation['status'] ? 'active' : 'inactive' ?>">

                                            <?= $designation['status'] ? 'Active' : 'Inactive' ?>

                                        </span>

                                    </td>

                                    <td>
                                        <?= htmlspecialchars($designation['created_at']) ?>
                                    </td>

                                    <td>

                                        <a
                                            href="edit.php?id=<?= $designation['id'] ?>"
                                            class="action-btn edit"
                                        >

                                            <i class="fa-solid fa-pen"></i>

                                        </a>

                                        <a
                                            href="delete.php?id=<?= $designation['id'] ?>"
                                            class="action-btn delete"
                                        >

                                            <i class="fa-solid fa-trash"></i>

                                        </a>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="6"
                                    style="text-align:center;padding:30px;"
                                >

                                    No Designation Found

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <?php if ($totalPages > 1): ?>

                <div class="pagination">

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                        <a
                            href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"
                            class="<?= ($page == $i) ? 'active' : '' ?>"
                        >

                            <?= $i ?>

                        </a>

                    <?php endfor; ?>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php include "../components/footer.php"; ?>
<?php

$page_title = "Departments";

include "../components/header.php";
include "../includes/database.php";

$search = trim($_GET['search'] ?? '');

/*
|--------------------------------------------------------------------------
| Department List Query
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        department_name,
        status,
        created_at
    FROM departments
";

$countSql = "
    SELECT COUNT(*)
    FROM departments
";

$where = "";
$params = [];

if ($search !== '') {

    $where = " WHERE department_name LIKE ? ";

    $keyword = "%{$search}%";

    $params = [$keyword];
}

$sql .= $where;
$countSql .= $where;

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

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

$sql .= " ORDER BY id DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$departments = $stmt->fetchAll();

?>

<div class="dashboard">

    <?php include "../components/sidebar.php"; ?>

    <div class="main">

        <?php include "../components/navbar.php"; ?>

        <div class="content">

            <!-- Page Header -->

            <div class="page-header">

                <h1>Departments</h1>

                <a href="add.php" class="add-btn">

                    <i class="fa-solid fa-plus"></i>

                    Add Department

                </a>

            </div>


            <!-- Search -->

            <div class="search-bar">

                <form method="GET">

                    <input
                        type="text"
                        name="search"
                        placeholder="Search department..."
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


            <!-- Department Table -->

            <div class="table-container">

                <table class="employee-table">

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Department Name</th>

                            <th>Status</th>

                            <th>Created At</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (count($departments) > 0): ?>

                            <?php foreach ($departments as $department): ?>

                                <tr>

                                    <td>
                                        <?= htmlspecialchars($department['id']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($department['department_name']) ?>
                                    </td>

                                    <td>

                                        <span class="status <?= $department['status'] ? 'active' : 'inactive' ?>">

                                            <?= $department['status'] ? 'Active' : 'Inactive' ?>

                                        </span>

                                    </td>

                                    <td>

                                        <?= htmlspecialchars($department['created_at']) ?>

                                    </td>

                                    <td>

                                        <a
                                            href="edit.php?id=<?= $department['id'] ?>"
                                            class="action-btn edit"
                                        >

                                            <i class="fa-solid fa-pen"></i>

                                        </a>

                                        <a
                                            href="delete.php?id=<?= $department['id'] ?>"
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
                                    colspan="5"
                                    style="text-align:center;padding:30px;"
                                >

                                    No Department Found

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>


            <!-- Pagination -->

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
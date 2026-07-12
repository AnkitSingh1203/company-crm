<?php

$page_title = "Employees";

include "../components/header.php";

?>

<?php

include "../includes/database.php";
$search = trim($_GET['search'] ?? '');

$sql = "
SELECT
    e.*,
    u.email,
    u.status,
    d.department_name,
    dg.designation_name
FROM employees e
LEFT JOIN users u ON e.user_id = u.id
LEFT JOIN departments d ON e.department_id = d.id
LEFT JOIN designations dg ON e.designation_id = dg.id
";

$countSql = "
SELECT COUNT(*) 
FROM employees e
LEFT JOIN users u ON e.user_id = u.id
";
$where = "";
$params = [];

if ($search != '') {

    $where = " WHERE
        e.employee_code LIKE ?
        OR e.first_name LIKE ?
        OR e.last_name LIKE ?
        OR CONCAT(e.first_name,' ',e.last_name) LIKE ?
        OR u.email LIKE ?";

    $keyword = "%{$search}%";

    $params = [
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword
    ];
}

$sql .= $where;
$countSql .= $where;

$limit = 10;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $limit;

$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);

$totalRecords = $countStmt->fetchColumn();

$totalPages = ceil($totalRecords / $limit);

$sql .= " ORDER BY e.id DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$employees = $stmt->fetchAll();
?>

<div class="dashboard">


    <?php include "../components/sidebar.php"; ?>


    <div class="main">


        <?php include "../components/navbar.php"; ?>


        <div class="content">


            <div class="page-header">

                <h1>Employees</h1>

                <a href="add.php" class="add-btn">

                    <i class="fa-solid fa-plus"></i>

                    Add Employee

                </a>

            </div>

            <div class="search-bar">

                <form method="GET">

                    <input
                        type="text"
                        name="search"
                        placeholder="Search employee..."
                        value="<?= htmlspecialchars($search) ?>">
                    <input type="hidden" name="page" value="1">
                    <button type="submit">

                        <i class="fa fa-search"></i>

                    </button>

                </form>

            </div>
            <div class="table-container">

                <table class="employee-table">

                    <thead>

                        <tr>
                            <th>Photo</th>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>

                    </thead>


                    <tbody>

                        <?php if (count($employees) > 0): ?>

                            <?php foreach ($employees as $employee): ?>

                                <tr>

                                    <td>
                                        <?php if (!empty($employee['profile_photo'])): ?>

                                            <img src="../uploads/<?= htmlspecialchars($employee['profile_photo']) ?>" class="emp-photo">

                                        <?php else: ?>

                                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($employee['first_name']) ?>" class="emp-photo">

                                        <?php endif; ?>
                                    </td>

                                    <td><?= htmlspecialchars($employee['employee_code']) ?></td>

                                    <td><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></td>

                                    <td><?= htmlspecialchars($employee['department_name']) ?></td>

                                    <td><?= htmlspecialchars($employee['designation_name']) ?></td>

                                    <td><?= htmlspecialchars($employee['email']) ?></td>

                                    <td>
                                        <span class="status <?= strtolower($employee['status']) ?>">
                                            <?= htmlspecialchars($employee['status']) ?>
                                        </span>
                                    </td>

                                    <td>

                                        <a href="view.php?id=<?= $employee['id'] ?>" class="action-btn view">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        <a href="edit.php?id=<?= $employee['id'] ?>" class="action-btn edit">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>

                                        <a href="delete.php?id=<?= $employee['id'] ?>"
                                            class="action-btn delete delete-btn">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="8" style="text-align:center;padding:30px;">
                                    No Employee Found
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
                            class="<?= ($page == $i) ? 'active' : '' ?>">

                            <?= $i ?>

                        </a>

                    <?php endfor; ?>

                </div>

            <?php endif; ?>


        </div> <!-- content -->

    </div> <!-- main -->


</div> <!-- dashboard -->


<?php include "../components/footer.php"; ?>
<script>
    document.querySelectorAll('.delete-btn').forEach(button => {

        button.addEventListener('click', function(e) {

            e.preventDefault();

            let url = this.getAttribute('href');

            Swal.fire({

                title: 'Delete Employee?',

                text: "This action cannot be undone.",

                icon: 'warning',

                showCancelButton: true,

                confirmButtonColor: '#d33',

                cancelButtonColor: '#3085d6',

                confirmButtonText: 'Yes, Delete'

            }).then((result) => {

                if (result.isConfirmed) {

                    window.location.href = url;

                }

            });

        });

    });
</script>
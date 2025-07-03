<?php
require 'db.php';

// Ambil data kamar dari DB
$kamar = $conn->query("
   SELECT 
    b.kd_bangsal, 
    b.nm_bangsal, 
    COUNT(k.kd_kamar) AS jumlah_bed_kosong
FROM 
    bangsal b
INNER JOIN 
    kamar k ON b.kd_bangsal = k.kd_bangsal
GROUP BY 
    b.kd_bangsal, b.nm_bangsal;

");

$mapping = $conn->query("
    SELECT 
        m.id,
        m.id_kamar,
        b.nm_bangsal,
        m.id_tt,

        -- Tersedia (status = 'kosong')
        (
            SELECT COUNT(*) 
            FROM kamar k 
            WHERE k.kd_bangsal = m.id_kamar AND k.status = 'kosong'
        ) AS tersedia,

        -- Terpakai (status = 'isi')
        (
            SELECT COUNT(*) 
            FROM kamar k 
            WHERE k.kd_bangsal = m.id_kamar AND k.status = 'isi'
        ) AS terpakai

    FROM 
        mapping_siranap m
    JOIN 
        bangsal b ON m.id_kamar = b.kd_bangsal
");


?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Form Mapping</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="bg-light p-5">

    <div class="container">
        <h3 class="mb-4">Form Mapping Kamar dan Tempat Tidur</h3>

        <form id="mappingForm">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="kamar" class="form-label">Pilih Kamar:</label>
                    <select id="id_kamar" name="id_kamar" class="form-select" required>
                        <option value="">--Memuat kamar--</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="tt" class="form-label">Pilih Tempat Tidur:</label>
                    <select id="id_tt" name="id_tt" class="form-select" required>
                        <option value="">--Memuat tempat tidur--</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Mapping</button>
        </form>

        <div id="result" class="mt-3"></div>

        <h4>Data Mapping Saat Ini</h4>
        <table id="mappingTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kode Bangsal</th>
                    <th>Nama Bangsal</th>
                    <th>ID Tempat Tidur</th>
                    <th>Tersedia</th>
                    <th>Terpakai</th> <!-- Tambahan -->
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $mapping->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['id_kamar'] ?></td>
                        <td><?= $row['nm_bangsal'] ?></td>
                        <td><?= $row['id_tt'] ?></td>
                        <td><?= $row['tersedia'] ?></td>
                        <td><?= $row['terpakai'] ?></td> <!-- Tambahan -->
                        <td>
                            <button class="btn btn-danger btn-sm btn-hapus" data-id="<?= $row['id'] ?>">Hapus</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    </div>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>


    <script>
        $(document).ready(function () {
            // Ambil kamar dari database
            $.get('get_kamar.php', function (data) {
                let $kamar = $('#id_kamar');
                $kamar.empty().append('<option value="">--Pilih Bangsal--</option>');
                data.forEach(function (item) {
                    if (item.nm_bangsal !== 'TOTAL') {
                        $kamar.append(`<option value="${item.kd_bangsal}">${item.nm_bangsal}</option>`);
                    }
                });
            });


            // Ambil tempat tidur dari API
            $.get('get_tempat_tidur.php', function (res) {
                let $tt = $('#id_tt');
                $tt.empty().append('<option value="">--Pilih Tempat Tidur--</option>');
                res.fasyankes.forEach(function (item) {
                    if (item.id_t_tt && item.ruang) {
                        $tt.append(`<option value="${item.id_t_tt}">${item.ruang}</option>`);
                    }
                });
            });

            $('#mappingForm').on('submit', function (e) {
                e.preventDefault();
                console.log("Form sedang dikirim...");

                $.ajax({
                    url: 'simpan_mapping.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (res) {
                        console.log("Respon dari server:", res);
                        alert(res.message);
                        location.reload();
                    },
                    error: function (xhr) {
                        console.error("Gagal:", xhr.responseText);
                        alert('Gagal menyimpan data');
                    }
                });
            });



        });
        // Tombol hapus
        $(document).on('click', '.btn-hapus', function () {
            const id = $(this).data('id');
            if (confirm('Yakin ingin menghapus data ini?')) {
                $.ajax({
                    url: 'hapus_mapping.php',
                    method: 'POST',
                    data: { id: id },
                    success: function (res) {
                        alert(res.message);
                        location.reload();
                    },
                    error: function (xhr) {
                        alert('Gagal menghapus data');
                        console.error(xhr.responseText);
                    }
                });
            }
        });
        $('#mappingTable').DataTable();

    </script>

</body>

</html>
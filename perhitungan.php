<?php
require_once('includes/init.php');

$user_role = get_role();
if($user_role == 'admin') {

$page = "Perhitungan";
require_once('template/header.php');

mysqli_query($koneksi,"TRUNCATE TABLE hasil;");

$kriterias = mysqli_query($koneksi,"SELECT * FROM kriteria ORDER BY kode_kriteria ASC");			
$alternatifs = mysqli_query($koneksi,"SELECT * FROM alternatif");			

//Matrix Keputusan (X)
$matriks_x = array();
foreach($alternatifs as $alternatif):
	foreach($kriterias as $kriteria):
		
		$id_alternatif = $alternatif['id_alternatif'];
		$id_kriteria = $kriteria['id_kriteria'];
		if($kriteria['ada_pilihan']==1){
			$q4 = mysqli_query($koneksi,"SELECT sub_kriteria.nilai FROM penilaian JOIN sub_kriteria WHERE penilaian.nilai=sub_kriteria.id_sub_kriteria AND penilaian.id_alternatif='$alternatif[id_alternatif]' AND penilaian.id_kriteria='$kriteria[id_kriteria]'");
			$data = mysqli_fetch_array($q4);
			if(count($data) > 0){
				$nilai = $data['nilai'];
			}else{
				$nilai = 0;
			}
		}else{
			$q4 = mysqli_query($koneksi,"SELECT nilai FROM penilaian WHERE id_alternatif='$alternatif[id_alternatif]' AND id_kriteria='$kriteria[id_kriteria]'");
			$data = mysqli_fetch_array($q4);
			if(count($data) > 0){
				$nilai = $data['nilai'];
			}else{
				$nilai = 0;
			}
		}
		
		$matriks_x[$id_kriteria][$id_alternatif] = $nilai;
	endforeach;
endforeach;

//Matriks Ternormalisasi (R)
$matriks_r = array();
foreach($alternatifs as $alternatif):
	foreach($kriterias as $kriteria):
		
		$id_alternatif = $alternatif['id_alternatif'];
		$id_kriteria = $kriteria['id_kriteria'];
		
		$x = $matriks_x[$id_kriteria][$id_alternatif];
		$nilai_max = @(max($matriks_x[$id_kriteria]));
		$nilai_min = @(min($matriks_x[$id_kriteria]));
		$r = ($x-$nilai_min)/($nilai_max-$nilai_min);
		
		$matriks_r[$id_kriteria][$id_alternatif] = $r;
	endforeach;
endforeach;

//Matriks Normalisasi Terbobot & Nilai Preferensi
$matriks_t = array();
$nilai_p = array();
foreach($alternatifs as $alternatif):
	$id_alternatif = $alternatif['id_alternatif'];
	$total_t = 0;
	foreach($kriterias as $kriteria):
	
		$id_kriteria = $kriteria['id_kriteria'];
		$bobot = $kriteria['bobot'];
		
		$r = $matriks_r[$id_kriteria][$id_alternatif];
		$t = $r*$bobot;
		$total_t += $t;
		$matriks_t[$id_kriteria][$id_alternatif] = $t;
	endforeach;
	$nilai_p[$id_alternatif] = $total_t;
endforeach;
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-calculator"></i> Data Perhitungan</h1>
</div>


<div class="card shadow mb-4">
    <!-- /.card-header -->
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa fa-table"></i> Matriks Keputusan (X)</h6>
    </div>

    <div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-primary text-white">
					<tr align="center">
						<th width="5%" rowspan="2">No</th>
						<th>Nama Alternatif</th>
						<?php foreach ($kriterias as $kriteria): ?>
							<th><?= $kriteria['kode_kriteria'] ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php 
						$no=1;
						foreach ($alternatifs as $alternatif): ?>
					<tr align="center">
						<td><?= $no; ?></td>
						<td align="left"><?= $alternatif['nama'] ?></td>
						<?php
						foreach ($kriterias as $kriteria):
							$id_alternatif = $alternatif['id_alternatif'];
							$id_kriteria = $kriteria['id_kriteria'];
							echo '<td>';
							echo $matriks_x[$id_kriteria][$id_alternatif];
							echo '</td>';
						endforeach;
						?>
					</tr>
					<?php
						$no++;
						endforeach;
					?>
					<tr align="center" class="bg-light">
						<th colspan="2">MAX</th>
						<?php foreach ($kriterias as $kriteria): ?>
						<th>
						<?php 
							$id_kriteria = $kriteria['id_kriteria'];
							echo max($matriks_x[$id_kriteria]);
						?>
						</th>
						<?php endforeach; ?>
					</tr>
					<tr align="center" class="bg-light">
						<th colspan="2">MIN</th>
						<?php foreach ($kriterias as $kriteria): ?>
						<th>
						<?php 
							$id_kriteria = $kriteria['id_kriteria'];
							echo min($matriks_x[$id_kriteria]);
						?>
						</th>
						<?php endforeach; ?>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="card shadow mb-4">
    <!-- /.card-header -->
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa fa-table"></i> Normalisasi Matriks (R)</h6>
    </div>

    <div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-primary text-white">
					<tr align="center">
						<th width="5%" rowspan="2">No</th>
						<th>Nama Alternatif</th>
						<?php foreach ($kriterias as $kriteria): ?>
							<th><?= $kriteria['kode_kriteria'] ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php 
						$no=1;
						foreach ($alternatifs as $alternatif): ?>
					<tr align="center">
						<td><?= $no; ?></td>
						<td align="left"><?= $alternatif['nama'] ?></td>
						<?php
						foreach ($kriterias as $kriteria):
							$id_alternatif = $alternatif['id_alternatif'];
							$id_kriteria = $kriteria['id_kriteria'];
							echo '<td>';
							echo $matriks_r[$id_kriteria][$id_alternatif];
							echo '</td>';
						endforeach;
						?>
					</tr>
					<?php
						$no++;
						endforeach;
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="card shadow mb-4">
    <!-- /.card-header -->
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa fa-table"></i> Bobot Kriteria</h6>
    </div>

    <div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-primary text-white">
					<tr align="center">
						<?php foreach ($kriterias as $key): ?>
						<th><?= $key['kode_kriteria'] ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<tr align="center">
						<?php foreach ($kriterias as $key): ?>
						<td>
						<?php 
						echo $key['bobot'];
						?>
						</td>
						<?php endforeach; ?>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="card shadow mb-4">
    <!-- /.card-header -->
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa fa-table"></i> Matriks Normalisasi Terbobot</h6>
    </div>

    <div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-primary text-white">
					<tr align="center">
						<th width="5%" rowspan="2">No</th>
						<th>Nama Alternatif</th>
						<?php foreach ($kriterias as $kriteria): ?>
							<th><?= $kriteria['kode_kriteria'] ?></th>
						<?php endforeach; ?>
						<th>Nilai Preferensi (P)</th>
					</tr>
				</thead>
				<tbody>
					<?php 
						$no=1;
						foreach ($alternatifs as $alternatif):
						$id_alternatif = $alternatif['id_alternatif'];
						?>
					<tr align="center">
						<td><?= $no; ?></td>
						<td align="left"><?= $alternatif['nama'] ?></td>
						<?php
						foreach ($kriterias as $kriteria):
							$id_kriteria = $kriteria['id_kriteria'];
							echo '<td>';
							echo $matriks_t[$id_kriteria][$id_alternatif];
							echo '</td>';
						endforeach;
						echo '<td>';
						echo $nilai_p[$id_alternatif];
						echo '</td>';
						?>
					</tr>
					<?php
						mysqli_query($koneksi,"INSERT INTO hasil (id_hasil, id_alternatif, nilai) VALUES ('', '$id_alternatif', '$nilai_p[$id_alternatif]')");
						$no++;
						endforeach;
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<?php
require_once('template/footer.php');
}
else {
	header('Location: login.php');
}
?>
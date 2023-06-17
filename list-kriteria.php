<?php
require_once('includes/init.php');
cek_login($role = array(1));
$page = "Kriteria";
require_once('template/header.php');

if(isset($_GET['generate'])){
	if($_GET['generate'] == "1") {
		$kriterias = array();
		$q1 = mysqli_query($koneksi,"SELECT * FROM kriteria ORDER BY kode_kriteria ASC");			
		while($krit = mysqli_fetch_array($q1)){
			$kriterias[$krit['id_kriteria']]['id_kriteria'] = $krit['id_kriteria'];
			$kriterias[$krit['id_kriteria']]['prioritas'] = $krit['prioritas'];
		}
		foreach ($kriterias as $x){
			$total = count($kriterias);
			$b = 0;
			foreach ($kriterias as $y){
				if($y['prioritas'] >= $x['prioritas']){
					$b += 1/$y['prioritas'];
				}
			}
			$id_kriteria = $x['id_kriteria'];
			$bobot = $b/$total;
			mysqli_query($koneksi,"UPDATE kriteria SET bobot = '$bobot' WHERE id_kriteria = '$id_kriteria'");
		}
	}
}
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-cube"></i> Data Kriteria</h1>

	<div>
		<a href="tambah-kriteria.php" class="btn btn-success"> <i class="fa fa-plus"></i> Tambah Data </a>
		<a href="list-kriteria.php?generate=1" class="btn btn-primary"> <i class="fa fa-check"></i> Generate Bobot </a>
	</div>
</div>

<?php
$status = isset($_GET['status']) ? $_GET['status'] : '';
$msg = '';
switch($status):
	case 'sukses-baru':
		$msg = 'Data berhasil disimpan';
		break;
	case 'sukses-hapus':
		$msg = 'Data behasil dihapus';
		break;
	case 'sukses-edit':
		$msg = 'Data behasil diupdate';
		break;
endswitch;

if($msg):
	echo '<div class="alert alert-info">'.$msg.'</div>';
endif;
?>

<div class="card shadow mb-4">
    <!-- /.card-header -->
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa fa-table"></i> Daftar Data Kriteria</h6>
    </div>

    <div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
				<thead class="bg-primary text-white">
					<tr align="center">
						<th>No</th>
						<th>Kode Kriteria</th>
						<th>Nama Kriteria</th>
						<th>Prioritas</th>
						<th>Bobot</th>
						<th>Cara Penilaian</th>
						<th width="15%">Aksi</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$no = 1;
				$query = mysqli_query($koneksi,"SELECT * FROM kriteria ORDER BY kode_kriteria ASC");			
				while($data = mysqli_fetch_array($query)):
				?>
					<tr align="center">
						<td><?php echo $no; ?></td>
						<td><?php echo $data['kode_kriteria']; ?></td>
						<td align="left"><?php echo $data['nama']; ?></td>
						<td><?php echo $data['prioritas']; ?></td>
						<td><?php if($data['bobot'] != "0"){echo $data['bobot'];}else{echo "-";} ?></td>
						<td><?php echo ($data['ada_pilihan']) ? 'Pilihan Sub Kriteria': 'Input Langsung'; ?></td>							
						<td>
							<div class="btn-group" role="group">
								<a data-toggle="tooltip" data-placement="bottom" title="Edit Data" href="edit-kriteria.php?id=<?php echo $data['id_kriteria']; ?>" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
								<a  data-toggle="tooltip" data-placement="bottom" title="Hapus Data" href="hapus-kriteria.php?id=<?php echo $data['id_kriteria']; ?>" onclick="return confirm ('Apakah anda yakin untuk meghapus data ini')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
							</div>
						</td>
					</tr>
					<?php 
					$no++;
					endwhile; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<?php
require_once('template/footer.php');
?>
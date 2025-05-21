
          <?php
include '../conf/conf.php';
include '../phpqrcode/qrlib.php';

function isAuthenticated($user, $pass) {
    return $user === USERHYBRIDWEB && $pass === PASHYBRIDWEB;
}

function loadSetting() {
    return mysqli_fetch_array(bukaquery("SELECT nama_instansi, alamat_instansi, kabupaten, propinsi, kontak, email, logo FROM setting"));
}

function generateQR($text, $filename) {
    $dir = __DIR__ . '/temp/';
    if (!file_exists($dir)) mkdir($dir);
    QRcode::png($text, $filename, 'L', 4, 2);
}

function getBillingData() {
    return bukaquery(
        "SELECT 
            a.temp1, a.temp2, a.temp3, a.temp4,
        
            COALESCE(
              b.material, c.material, d.material, e.material, f.material
            ) AS Jasa[Hospital],
        
            COALESCE(
              b.tarif_tindakandr, c.tarif_tindakandr, d.tarif_tindakan_dokter, e.tarif_tindakan_dokter, f.tarif_tindakan_dokter
            ) AS tarifDR,
        
            COALESCE(
              b.tarif_tindakanpr, c.tarif_tindakanpr, d.tarif_tindakan_petugas, e.tarif_tindakan_petugas, f.tarif_tindakan_petugas
            ) AS tarifPR,
        
            a.temp5, a.temp6, a.temp7
        
        FROM temporary_bayar_ranap a
        
        LEFT JOIN jns_perawatan_inap b 
            ON b.kd_jenis_prw = SUBSTRING_INDEX(a.temp2, ' - ', 1)
        
        LEFT JOIN jns_perawatan c 
            ON c.kd_jenis_prw = SUBSTRING_INDEX(a.temp2, ' - ', 1)
        
        LEFT JOIN jns_perawatan_lab d 
            ON d.kd_jenis_prw = SUBSTRING_INDEX(a.temp2, ' - ', 1)
        
        LEFT JOIN jns_perawatan_radiologi e 
            ON e.kd_jenis_prw = SUBSTRING_INDEX(a.temp2, ' - ', 1)
        
        LEFT JOIN jns_perawatan_utd f 
            ON f.kd_jenis_prw = SUBSTRING_INDEX(a.temp2, ' - ', 1)
        
        ORDER BY a.no ASC
    ");
}

function formatBillingRow($row, $index, &$headerPrinted) {
    // Baris deskripsi awal seperti No.Nota, Pasien, dsb.
    if (!empty($row[0]) && empty($row[2]) && empty($row[3]) && empty($row[6]) && empty($row[9])) {
        return "<td width='15%' style='padding-left:5px; padding-right:5px;'><font size='1' face='Tahoma'>" . str_replace("  ", "&nbsp;&nbsp;", $row[0]) . "</font></td>
                <td colspan='8' style='padding-left:5px; padding-right:5px;'><font size='1' face='Tahoma'>{$row[1]}</font></td>";
    }

    // Baris total akhir
    if (empty($row[9]) && empty($row[7])) {
        return "<td width='10%' style='padding-left:5px; padding-right:5px;'><font size='1' face='Tahoma'>" . str_replace("   ", "&nbsp;&nbsp;", $row[0]) . "</font></td>
                <td colspan='8' align='right' style='padding-left:5px; padding-right:5px;'><font size='1' face='Tahoma'>{$row[1]}</font></td>";
    }

    // Header tabel utama
    $html = '';
    if (in_array(strtolower(trim($row[0])), ['Registrasi', 'Ruang'])) {
        return "<td colspan='10' style='padding-left:5px; padding-top:10px;'><b><font size='1' face='Tahoma'>"
                . strtoupper($row[0]) . "</font></b></td>";
    }

    if (!$headerPrinted) {
        $html .= "<tr>
                    <td width='10%' align='center'><b><font size='1' face='Tahoma'>Kategori</font></b></td>
                    <td align='center'><b><font size='1' face='Tahoma'>Tindakan / Nama Item</font></b></td>
                    <td align='center'><b><font size='1' face='Tahoma'>:</font></b></td>
                    <td align='center'><b><font size='1' face='Tahoma'>Biaya</font></b></td>
                    <td align='center'><b><font size='1' face='Tahoma'>Jasa [Hospital]</font></b></td>
                    <td align='center'><b><font size='1' face='Tahoma'>Tarif Dr</font></b></td>
                    <td align='center'><b><font size='1' face='Tahoma'>Tarif Pr</font></b></td>
                    <td align='center'><b><font size='1' face='Tahoma'>T</font></b></td>
                    <td width='1%' align='center'><b><font size='1' face='Tahoma'>Jml</font></b></td>
                    <td align='center'><b><font size='1' face='Tahoma'>Tambahan</font></b></td>
                    <td align='center'><b><font size='1' face='Tahoma'>Total</font></b></td>
                  </tr>";
        $headerPrinted = true;
    }

    // Sembunyikan baris kosong
    if (floatval($row[3]) == 0 && floatval($row[4]) == 0 && floatval($row[5]) && floatval($row[6]) == 0 && floatval($row[9]) == 0) {
        return '';
    }

    // Format baris data
    $html .= "<tr>
                <td style='padding-left:5px; padding-right:5px;'><font size='1' face='Tahoma'>{$row[0]}</font></td>
                <td style='padding-left:5px; padding-right:5px;'><font size='1' face='Tahoma'>{$row[1]}</font></td>
                <td align='center'><font size='1' face='Tahoma'>{$row[2]}</font></td>
                <td align='center'><font size='1' face='Tahoma'>{$row[3]}</font></td>
                <td align='center'><font size='1' face='Tahoma'>" . number_format($row[4]) . "</font></td>
                <td align='center'><font size='1' face='Tahoma'>" . number_format($row[5]) . "</font></td>
                <td align='center'><font size='1' face='Tahoma'>" . number_format($row[6]) . "</font></td>
                <td align='center'><font size='1' face='Tahoma'>{$row[7]}</font></td>
                <td align='center'><font size='1' face='Tahoma'>{$row[8]}</font></td>
                <td align='right'><font size='1' face='Tahoma'>{$row[9]}</font></td>
              </tr>";
    return $html;
}


function createQRText($petugas, $setting, $tanggal) {
    $kabupaten = $setting['kabupaten'];
    $namaInstansi = $setting['nama_instansi'];

    if (getOne("SELECT COUNT(petugas.nama) FROM petugas WHERE nip='$petugas'") >= 1) {
        $namaPetugas = getOne("SELECT nama FROM petugas WHERE nip='$petugas'");
        $idPetugas = getOne3("SELECT IFNULL(SHA1(sidikjari.sidikjari), '$petugas') FROM sidikjari INNER JOIN pegawai ON pegawai.id=sidikjari.id WHERE pegawai.nik='$petugas'", $petugas);
        return [$namaPetugas, "Dikeluarkan di $namaInstansi, Kabupaten/Kota $kabupaten\nDitandatangani secara elektronik oleh $namaPetugas\nID $idPetugas\n$tanggal"];
    }
    return ["Admin Utama", "Dikeluarkan di $namaInstansi, Kabupaten/Kota $kabupaten\nDitandatangani secara elektronik oleh Admin Utama\nID ADMIN\n$tanggal"];
}

// Start
$usere = trim($_GET['usere'] ?? '');
$passwordte = trim($_GET['passwordte'] ?? '');

if (!isAuthenticated($usere, $passwordte)) {
    header("Location:../index.php");
    exit;
}

$petugas = validTeks4(str_replace("_", " ", $_GET['petugas'] ?? ''), 20);
$tanggal = validTeks4(str_replace("_", " ", $_GET['tanggal'] ?? ''), 20);

$setting = loadSetting();
$billingData = getBillingData();
$lebarNota = getOne("SELECT nota1ranap FROM set_nota");

if (mysqli_num_rows($billingData) === 0) {
    die("<font color='333333' size='1' face='Times New Roman'><b>Data Billing masih kosong!</b></font>");
}

// QR
list($namaTTD, $qrText) = createQRText($petugas, $setting, $tanggal);
$filename = __DIR__ . "/temp/$petugas.png";
generateQR($qrText, $filename);
$qrImage = "<img width='50' height='50' src='temp/" . basename($filename) . "'/>";

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Billing Ranap</title>
    <link href="style.css" rel="stylesheet" type="text/css" media="screen">
    <script>window.onload = () => window.print();</script>
</head>
<body style="background-color: #ffffff;">
    <table width="<?= $lebarNota ?>" border="1" cellpadding="0" cellspacing="0">
        <tr class="isi12">
            <td colspan="10">
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="20%"><img width="90" height="45" src="data:image/jpeg;base64,<?= base64_encode($setting['logo']) ?>"></td>
                        <td align="center">
                            <font size="3" face="Tahoma"><?= $setting['nama_instansi'] ?></font><br>
                            <font size="1" face="Tahoma">
                                <?= $setting['alamat_instansi'] ?>, <?= $setting['kabupaten'] ?>, <?= $setting['propinsi'] ?><br>
                                <?= $setting['kontak'] ?>, E-mail: <?= $setting['email'] ?>
                            </font>
                        </td>
                        <td width="20%"><font size="2" face="Tahoma"><?= $carabayar = getOne("SELECT png_jawab FROM penjab WHERE kd_pj='".getOne("SELECT kd_pj FROM reg_periksa WHERE no_rawat='".getOne("SELECT no_rawat FROM nota_inap WHERE no_nota='".str_replace(": ", "", getOne("SELECT temp2 FROM temporary_bayar_ranap WHERE temp1='No.Nota'"))."'")."'")."'") ?>"</font></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr><td colspan="10"><hr><center><font size="1" face="Tahoma">BILLING</font></center></td></tr>

        <?php
        $index = 1;
        while ($row = mysqli_fetch_array($billingData)) {
            echo "<tr class='isi12'>" . formatBillingRow($row, $index, $headerPrinted) . "</tr>";
            $index++;
        }
        ?>
    </table>
</body>
</html>

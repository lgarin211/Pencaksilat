<!-- api2.php?a=get_data_view_tanding&id_partai=1 -->
<?php
include "../backend/includes/connection.php";

$biru = 0;
$merah = 0;
$data = [];

if (!empty($_GET['a']) && $_GET['a'] == 'get_data_view_tanding' && $_GET['id_partai'] != 0) {
    $tag = $_GET['tag']; 
    $query = "SELECT * FROM validscrore WHERE babak='$tag' ORDER BY timeline";
    $result = mysqli_query($koneksi, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            if($row['sudut']=="MERAH"){
                $biru+=$row['nilai'];
            }else{
                $merah+=$row['nilai'];
            }
            $data[] = $row;
        }
        mysqli_free_result($result);
    } else {
        echo "Error in query: " . mysqli_error($koneksi);
    }
}
?>
<div class="text-center" style="font-size: 250px; color: white">
  <div class="row">
    <div class="col-5" style="background-color: #ff0000">
      <b><b><?=$biru;?></b></b>
    </div>
    <div class="col-2">
      <div class="row">
        <div class="col-12 mt-3">
          <button class="btn btn-primary" onclick="regp(1)">Babak 1</button>
        </div>
        <div class="col-12 mt-3">
          <button class="btn btn-primary" onclick="regp(2)">Babak 2</button>
        </div>
        <div class="col-12 mt-3">
          <button class="btn btn-primary" onclick="regp(3)">Babak 3</button>
        </div>
        <div class="col-12 mt-5">
            <input type="text text-center" class="form-control" value="BABAK <?=$_GET['tag']?>" >
        </div>
      </div>
    </div>
    <div class="col-5" style="background-color: #0000ff">
      <b><b><?=$merah;?></b></b>
    </div>
  </div>
</div>
<!DOCTYPE html>
<html>

<head>
  <title>二酸化炭素濃度</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .container {
      max-width: 1200px;
    }

    canvas {
      max-width: 100%;
    }

    #dateSelector {
      max-width: 50%;
    }
  </style>
</head>

<body>

  <div class="container">
    <h1 class="mt-4">二酸化炭素濃度</h1>

    <div class="row">
      <!-- PHP 部分 -->
      <div class="col-12">
        <?php
          date_default_timezone_set('Asia/Tokyo');
          $lines = file('/home/23TE/web/co2.ditu.jp/public_html/co2/output.json', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
          $lastLine = end($lines);
          $lastData = json_decode($lastLine, true);
          $deviceNames = [];
        ?>
        <h2 class="mt-4">最新のデータ:</h2>
        <div class="row">
          <div class="col-12">
            <p>日時: <?= $lastData['datetime'] ?></p>
          </div>
          <div class="col-12">
            <div class="row">
              <?php
                foreach ($lastData as $key => $value) {
                  if ($key !== 'datetime' && strpos($key, 'CO2') !== false) {
                    $deviceNames[] = $key;
                    echo '<div class="col-4">';
                    echo "<p>{$key}: {$value}</p>";
                    echo '</div>';
                  }
                }
                $jsonData = json_encode($lines);
                $deviceNamesJson = json_encode($deviceNames);
              ?>
            </div>
          </div>
        </div>
      </div>

      <!-- 日期选择器 -->
      <div class="col-md-6 col-sm-12">
        <h2>日付を選択して：</h2>
        <input type="date" id="dateSelector" class="form-control mb-4">
      </div>
    </div>

    <!-- 图表 -->
    <div class="row">
      <div class="col-12">
        <h2 class="mt-4 text-center">二酸化炭素濃度推移図:</h2>
        <canvas id="myChart"></canvas>
      </div>
    </div>
  </div>

  <!-- JavaScript 部分 -->
  <script>
    const rawData = <?php echo $jsonData; ?>;
    const deviceNames = <?php echo $deviceNamesJson; ?>;
    let myChart;

    function updateChart(selectedDate) {
      const labels = [];
      const sensorData = {};

      deviceNames.forEach((name) => {
        sensorData[name] = [];
      });

      rawData.forEach((line) => {
        const parsed = JSON.parse(line);
        if (!parsed.datetime) return;

        const dataDate = parsed.datetime.split(' ')[0];
        if (dataDate === selectedDate) {
          labels.push(parsed.datetime);
          deviceNames.forEach((name) => {
            if (parsed[name]) {
              sensorData[name].push(parsed[name]);
            }
          });
        }
      });

      const datasets = [];
      deviceNames.forEach((name) => {
        datasets.push({
          label: name,
          data: sensorData[name],
          borderColor: `rgba(${Math.random() * 255}, ${Math.random() * 255}, ${Math.random() * 255}, 1)`,
          fill: false,
        });
      });

      const ctx = document.getElementById('myChart').getContext('2d');

      if (myChart) {
        myChart.destroy();
      }

      myChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: datasets,
        },
      });
    }

    document.addEventListener('DOMContentLoaded', function () {
      const dateSelector = document.getElementById('dateSelector');
      const selectedDate = new Date().toISOString().split('T')[0];
      dateSelector.value = selectedDate;

      dateSelector.addEventListener('change', function () {
        updateChart(this.value);
      });

      updateChart(selectedDate);
    });
  </script>

</body>

</html>

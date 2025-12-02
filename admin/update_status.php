<?php
session_start();
include '../koneksi.php';

// Enable error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
  header('Content-Type: application/json');
  echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
  exit;
}

// Set header JSON
header('Content-Type: application/json');

// Pastikan request method adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
  exit;
}

// Ambil action
$action = $_POST['action'] ?? '';

// ========== UPDATE STATUS ==========
if ($action === 'update') {
  try {
    // Validasi input
    if (!isset($_POST['queue_id']) || !isset($_POST['status'])) {
      echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
      exit;
    }

    // Ambil data dari POST
    $queue_id = intval($_POST['queue_id']);
    $status = trim($_POST['status']);
    $technician_id = isset($_POST['technician_id']) && $_POST['technician_id'] !== '' ? intval($_POST['technician_id']) : null;

    error_log("=== UPDATE STATUS START ===");
    error_log("Queue ID: $queue_id");
    error_log("Status: $status");
    error_log("Technician ID: " . ($technician_id ?? 'NULL'));

    // Validasi data tidak kosong
    if (empty($queue_id) || empty($status)) {
      echo json_encode(['success' => false, 'message' => 'Queue ID dan Status harus diisi!']);
      exit;
    }

    // Validasi status value
    $allowed_status = ['Menunggu', 'Diproses', 'Selesai'];
    if (!in_array($status, $allowed_status)) {
      echo json_encode(['success' => false, 'message' => 'Status tidak valid!']);
      exit;
    }

    // Validasi teknisi WAJIB untuk status Diproses dan Selesai
    if (($status === 'Diproses' || $status === 'Selesai') && empty($technician_id)) {
      echo json_encode(['success' => false, 'message' => 'Teknisi harus dipilih untuk status ' . $status]);
      exit;
    }

    // Cek apakah queue_id ada di database
    $check_stmt = $conn->prepare("SELECT queue_id, queue_number, status, id_menu, customer_id FROM queue WHERE queue_id = ?");
    $check_stmt->bind_param("i", $queue_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
      echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemukan']);
      exit;
    }

    $old_data = $check_result->fetch_assoc();
    $check_stmt->close();

    error_log("Old status: " . $old_data['status']);
    error_log("Service ID (id_menu): " . $old_data['id_menu']);

    // Proses update status berdasarkan kondisi
    $update_success = false;
    $error_msg = '';
    
    if ($status === 'Diproses') {
      // Status Diproses: Set technician_id dan processed_at
      $stmt = $conn->prepare("UPDATE queue SET 
                              status = ?, 
                              technician_id = ?, 
                              processed_at = NOW(),
                              completed_at = NULL
                              WHERE queue_id = ?");
      
      if ($stmt === false) {
        error_log("Prepare failed (Diproses): " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
      }
      
      $stmt->bind_param("sii", $status, $technician_id, $queue_id);
      $update_success = $stmt->execute();
      
      if (!$update_success) {
        $error_msg = $stmt->error;
        error_log("Execute failed (Diproses): " . $error_msg);
      } else {
        error_log("✅ Status berhasil diupdate ke Diproses");
      }
      
      $stmt->close();
    } 
    elseif ($status === 'Selesai') {
      // Status Selesai: Set technician_id dan completed_at
      $stmt = $conn->prepare("UPDATE queue SET 
                              status = ?, 
                              technician_id = ?, 
                              completed_at = NOW()
                              WHERE queue_id = ?");
      
      if ($stmt === false) {
        error_log("Prepare failed (Selesai): " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
      }
      
      $stmt->bind_param("sii", $status, $technician_id, $queue_id);
      $update_success = $stmt->execute();
      
      if (!$update_success) {
        $error_msg = $stmt->error;
        error_log("Execute failed (Selesai): " . $error_msg);
      } else {
        error_log("✅ Status berhasil diupdate ke Selesai");
      }
      
      $stmt->close();
      
            // ✅ SIMPAN KE TABEL REPAIR (minimal perubahan, aman)
      if ($update_success) {
        error_log("=== MENYIMPAN KE REPAIR ===");

        try {
          // Ambil data queue + customer + menu (tanpa join ke service)
          $repair_query = "SELECT q.*, 
                              COALESCE(c.name, q.nama, 'Tidak tersedia') AS customer_name,
                              m.name AS service_name
                          FROM queue q
                          LEFT JOIN customer c ON q.customer_id = c.customer_id
                          LEFT JOIN menu m ON q.id_menu = m.id_menu
                          WHERE q.queue_id = ?";

          $repair_stmt = $conn->prepare($repair_query);

          if ($repair_stmt === false) {
            error_log("❌ Repair query prepare failed: " . $conn->error);
          } else {
            $repair_stmt->bind_param("i", $queue_id);
            $repair_stmt->execute();
            $repair_result = $repair_stmt->get_result();

            if ($repair_result->num_rows > 0) {
              $queue_data = $repair_result->fetch_assoc();

              error_log("Data queue ditemukan:");
              error_log("- Queue ID: " . $queue_data['queue_id']);
              error_log("- id_menu: " . $queue_data['id_menu']);
              error_log("- Customer: " . $queue_data['customer_name']);
              error_log("- Service (menu): " . $queue_data['service_name']);

              // --- dapati service_id yg terkait dengan queue (jika sudah ada) ---
              $service_id = null;
              $svc_stmt = $conn->prepare("SELECT service_id FROM service WHERE queue_id = ?");
              if ($svc_stmt !== false) {
                $svc_stmt->bind_param("i", $queue_id);
                $svc_stmt->execute();
                $svc_res = $svc_stmt->get_result();
                if ($svc_res && $svc_res->num_rows > 0) {
                  $svc_row = $svc_res->fetch_assoc();
                  $service_id = (int)$svc_row['service_id'];
                  error_log("Found existing service_id: " . $service_id);
                }
                $svc_stmt->close();
              } else {
                error_log("❌ Prepare select service by queue_id failed: " . $conn->error);
              }

              // --- jika belum ada service, buat baru ---
              if (empty($service_id)) {
                error_log("No service found for this queue. Creating new service row...");
                $ins_svc = $conn->prepare("
                  INSERT INTO service 
                  (queue_id, customer_id, technician_handler_id, date_received, damage_description, estimated_cost, estimated_time, service_status)
                  VALUES (?, ?, ?, NOW(), ?, NULL, NULL, 'Selesai')
                ");
                if ($ins_svc !== false) {
                  $cust_id = $queue_data['customer_id'];
                  $tech_hdl = $technician_id; // dari input sebelumnya
                  $desc = !empty($queue_data['keluhan']) ? $queue_data['keluhan'] : $queue_data['service_name'];

                  $ins_svc->bind_param("iiis", $queue_id, $cust_id, $tech_hdl, $desc);

                  if ($ins_svc->execute()) {
                    $service_id = (int)$conn->insert_id;
                    error_log("✅ Created service_id: " . $service_id);
                  } else {
                    error_log("❌ Failed to insert service: " . $ins_svc->error);
                  }

                  $ins_svc->close();
                } else {
                  error_log("❌ Prepare insert service failed: " . $conn->error);
                }
              }

              // Jika masih tidak ada service_id, log dan skip insert repair
              if (empty($service_id)) {
                error_log("❌ service_id masih kosong setelah upaya create. Tidak melakukan insert ke repair.");
              } else {
                // Cek apakah sudah ada repair untuk queue_id
                $check_repair = $conn->prepare("SELECT repair_id FROM repair WHERE queue_id = ?");
                if ($check_repair !== false) {
                  $check_repair->bind_param("i", $queue_id);
                  $check_repair->execute();
                  $check_result = $check_repair->get_result();

                  // Siapkan description
                  $description = !empty($queue_data['description']) 
                    ? $queue_data['description'] 
                    : 'Service ' . $queue_data['customer_name'] . ' - ' . $queue_data['service_name'] . ' (Queue #' . $queue_data['queue_number'] . ')';

                  if ($check_result->num_rows == 0) {
                    // INSERT BARU
                    error_log(">>> Belum ada di repair, INSERT baru");
                    $insert_repair = "INSERT INTO repair 
                                      (queue_id, service_id, technician_performer_id, start_date, description, is_warranty_used) 
                                      VALUES (?, ?, ?, NOW(), ?, 'No')";
                    $insert_stmt = $conn->prepare($insert_repair);

                    if ($insert_stmt !== false) {
                      $insert_stmt->bind_param("iiis", $queue_id, $service_id, $technician_id, $description);

                      error_log("Binding params for insert_repair:");
                      error_log("- queue_id: $queue_id");
                      error_log("- service_id: $service_id");
                      error_log("- technician_id: $technician_id");

                      if ($insert_stmt->execute()) {
                        $new_repair_id = $conn->insert_id;
                        error_log("✅✅✅ BERHASIL INSERT ke repair! repair_id: $new_repair_id");
                      } else {
                        error_log("❌ Execute INSERT repair failed: " . $insert_stmt->error);
                      }

                      $insert_stmt->close();
                    } else {
                      error_log("❌ Prepare INSERT repair failed: " . $conn->error);
                    }
                  } else {
                    // UPDATE yang existing (jika diperlukan)
                    error_log(">>> Sudah ada di repair, UPDATE");
                    $update_repair = "UPDATE repair SET 
                                      service_id = ?,
                                      technician_performer_id = ?,
                                      start_date = NOW(),
                                      description = ?
                                      WHERE queue_id = ?";

                    $update_stmt = $conn->prepare($update_repair);
                    if ($update_stmt !== false) {
                      $update_stmt->bind_param("iisi", $service_id, $technician_id, $description, $queue_id);
                      if ($update_stmt->execute()) {
                        error_log("✅ UPDATE repair berhasil untuk queue_id: $queue_id");
                      } else {
                        error_log("❌ Execute UPDATE repair failed: " . $update_stmt->error);
                      }
                      $update_stmt->close();
                    } else {
                      error_log("❌ Prepare UPDATE repair failed: " . $conn->error);
                    }
                  }

                  $check_repair->close();
                } else {
                  error_log("❌ Check repair prepare failed: " . $conn->error);
                }
              } // end if service_id exists
            } else {
              error_log("❌ Data queue tidak ditemukan untuk queue_id: $queue_id");
            }

            $repair_stmt->close();
          }
        } catch (Exception $e) {
          error_log("❌ Exception saat menyimpan ke repair: " . $e->getMessage());
          error_log("Stack trace: " . $e->getTraceAsString());
        }

        error_log("=== SELESAI MENYIMPAN KE REPAIR ===");
      }

    } 
    else {
      // Status Menunggu: Reset semua
      $stmt = $conn->prepare("UPDATE queue SET 
                              status = ?, 
                              technician_id = NULL,
                              processed_at = NULL,
                              completed_at = NULL
                              WHERE queue_id = ?");
      
      if ($stmt === false) {
        error_log("Prepare failed (Menunggu): " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
      }
      
      $stmt->bind_param("si", $status, $queue_id);
      $update_success = $stmt->execute();
      
      if (!$update_success) {
        $error_msg = $stmt->error;
        error_log("Execute failed (Menunggu): " . $error_msg);
      } else {
        error_log("✅ Status berhasil diupdate ke Menunggu");
      }
      
      $stmt->close();
    }

    if ($update_success) {
      // Ambil tanggal terbaru
      $date_query = "SELECT 
                      queue_number,
                      status,
                      CASE 
                        WHEN status = 'Selesai' AND completed_at IS NOT NULL 
                          THEN DATE_FORMAT(completed_at, '%d/%m/%Y %H:%i')
                        WHEN status = 'Diproses' AND processed_at IS NOT NULL 
                          THEN DATE_FORMAT(processed_at, '%d/%m/%Y %H:%i')
                        ELSE DATE_FORMAT(created_at, '%d/%m/%Y %H:%i')
                      END as display_date
                     FROM queue 
                     WHERE queue_id = ?";
      
      $date_stmt = $conn->prepare($date_query);
      $date_stmt->bind_param("i", $queue_id);
      $date_stmt->execute();
      $date_result = $date_stmt->get_result();
      
      if ($date_result->num_rows > 0) {
        $date_row = $date_result->fetch_assoc();
        $date_stmt->close();
        
        $success_message = 'Status pesanan #' . $old_data['queue_number'] . ' berhasil diubah menjadi "' . $status . '"';
        
        if ($status === 'Selesai') {
          $success_message .= ' dan tersimpan ke riwayat repair';
        }
        
        error_log("=== UPDATE STATUS SUCCESS ===");
        
        echo json_encode([
          'success' => true, 
          'message' => $success_message,
          'updated_date' => $date_row['display_date'],
          'data' => [
            'queue_id' => $queue_id,
            'queue_number' => $old_data['queue_number'],
            'old_status' => $old_data['status'],
            'new_status' => $status,
            'technician_id' => $technician_id,
            'saved_to_repair' => ($status === 'Selesai')
          ]
        ]);
      } else {
        echo json_encode([
          'success' => false, 
          'message' => 'Gagal mengambil data tanggal terbaru'
        ]);
      }
    } else {
      echo json_encode([
        'success' => false, 
        'message' => 'Gagal mengupdate status: ' . ($error_msg ? $error_msg : 'Unknown error')
      ]);
    }
    
  } catch (Exception $e) {
    error_log("Exception in update_status: " . $e->getMessage());
    echo json_encode([
      'success' => false, 
      'message' => 'Error: ' . $e->getMessage()
    ]);
  }
}

// ========== DELETE PESANAN ==========
elseif ($action === 'delete') {
  // Validasi input
  if (!isset($_POST['queue_id'])) {
    echo json_encode(['success' => false, 'message' => 'Queue ID tidak ditemukan']);
    exit;
  }

  $queue_id = intval($_POST['queue_id']);

  if (empty($queue_id)) {
    echo json_encode(['success' => false, 'message' => 'Queue ID tidak valid!']);
    exit;
  }

  // Cek apakah pesanan ada
  $check_stmt = $conn->prepare("SELECT queue_id, queue_number, status FROM queue WHERE queue_id = ?");
  $check_stmt->bind_param("i", $queue_id);
  $check_stmt->execute();
  $check_result = $check_stmt->get_result();

  if ($check_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemukan']);
    exit;
  }

  $queue_data = $check_result->fetch_assoc();
  $check_stmt->close();

  // Hapus pesanan dari queue
  $delete_stmt = $conn->prepare("DELETE FROM queue WHERE queue_id = ?");
  $delete_stmt->bind_param("i", $queue_id);

  if ($delete_stmt->execute()) {
    if ($delete_stmt->affected_rows > 0) {
      $message = 'Pesanan #' . $queue_data['queue_number'] . ' berhasil dihapus';
      
      if (strtolower($queue_data['status']) === 'selesai') {
        $message .= '. Data tetap tersimpan di riwayat repair.';
      }
      
      echo json_encode([
        'success' => true, 
        'message' => $message,
        'data' => [
          'queue_id' => $queue_id,
          'queue_number' => $queue_data['queue_number'],
          'was_completed' => (strtolower($queue_data['status']) === 'selesai')
        ]
      ]);
    } else {
      echo json_encode([
        'success' => false, 
        'message' => 'Tidak ada data yang dihapus'
      ]);
    }
  } else {
    echo json_encode([
      'success' => false, 
      'message' => 'Gagal menghapus pesanan: ' . $conn->error
    ]);
  }

  $delete_stmt->close();
}

else {
  echo json_encode(['success' => false, 'message' => 'Action tidak valid']);
}

$conn->close();
?>
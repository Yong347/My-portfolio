<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

include 'db.php';

// form for the submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (isset($_POST['update_content'])) {
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'content_') === 0) {
                $content_key = str_replace('content_', '', $key);
                $section = $_POST['section_' . $content_key];
                
                // Update or insert content
                $stmt = $conn->prepare("INSERT INTO portfolio_content (section_name, content_key, content_value) 
                                       VALUES (?, ?, ?) 
                                       ON DUPLICATE KEY UPDATE content_value = ?");
                $stmt->bind_param("ssss", $section, $content_key, $value, $value);
                $stmt->execute();
            }
        }
        $success = "Content updated successfully!";
    }
    
    // for project deletion
    if (isset($_POST['delete_project'])) {
        $project_num = $_POST['project_to_delete'];
        
        if (!empty($project_num) && is_numeric($project_num)) {
            
            $keys_to_delete = [
                "project{$project_num}_title",
                "project{$project_num}_desc", 
                "project{$project_num}_link",
                "project{$project_num}_image"
            ];
            
            $stmt = $conn->prepare("DELETE FROM portfolio_content WHERE section_name = ? AND content_key = ?");
            $section = "projects";
            
            foreach ($keys_to_delete as $key) {
                $stmt->bind_param("ss", $section, $key);
                $stmt->execute();
            }
            
            $stmt->close();
            
           
            reorganizeProjects($conn);
            
            $success = "Project deleted successfully!";
            
            
            header("Location: admin_panel.php");
            exit;
        }
    }
    
    // for message deletion
    if (isset($_POST['delete_message'])) {
        $message_id = $_POST['message_id'];
        
        if (!empty($message_id)) {
            $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
            $stmt->bind_param("i", $message_id);
            
            if ($stmt->execute()) {
                $success = "Message deleted successfully!";
            } else {
                $error = "Error deleting message!";
            }
            
            $stmt->close();
        }
    }
    
    // I apply CRUD in my portfolio
    if (isset($_POST['add_experience'])) {
        $title = $_POST['new_experience_title'];
        $description = $_POST['new_experience_description'];
        $start_date = $_POST['new_experience_start_date'];
        $end_date = $_POST['new_experience_end_date'];
        
        if (!empty($title) && !empty($description)) {
            $stmt = $conn->prepare("INSERT INTO experience (title, description, start_date, end_date) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $title, $description, $start_date, $end_date);
            
            if ($stmt->execute()) {
                $success = "Experience added successfully!";
            } else {
                $error = "Error adding experience!";
            }
            $stmt->close();
        }
    }
    
    if (isset($_POST['update_experience'])) {
        $exp_id = $_POST['experience_id'];
        $title = $_POST['experience_title'];
        $description = $_POST['experience_description'];
        $start_date = $_POST['experience_start_date'];
        $end_date = $_POST['experience_end_date'];
        
        $stmt = $conn->prepare("UPDATE experience SET title=?, description=?, start_date=?, end_date=? WHERE id=?");
        $stmt->bind_param("ssssi", $title, $description, $start_date, $end_date, $exp_id);
        
        if ($stmt->execute()) {
            $success = "Experience updated successfully!";
        } else {
            $error = "Error updating experience!";
        }
        $stmt->close();
    }
    
    if (isset($_POST['delete_experience'])) {
        $exp_id = $_POST['experience_id'];
        
        $stmt = $conn->prepare("DELETE FROM experience WHERE id=?");
        $stmt->bind_param("i", $exp_id);
        
        if ($stmt->execute()) {
            $success = "Experience deleted successfully!";
        } else {
            $error = "Error deleting experience!";
        }
        $stmt->close();
    }
    
    // for certifications CRUD
    if (isset($_POST['add_certification'])) {
        $name = $_POST['new_certification_name'];
        $issuer = $_POST['new_certification_issuer'];
        $issued_date = $_POST['new_certification_issued_date'];
        $credential_url = $_POST['new_certification_url'];
        
        if (!empty($name) && !empty($issuer)) {
            $stmt = $conn->prepare("INSERT INTO certifications (name, issuer, issued_date, credential_url) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $issuer, $issued_date, $credential_url);
            
            if ($stmt->execute()) {
                $success = "Certification added successfully!";
            } else {
                $error = "Error adding certification!";
            }
            $stmt->close();
        }
    }
    
    if (isset($_POST['update_certification'])) {
        $cert_id = $_POST['certification_id'];
        $name = $_POST['certification_name'];
        $issuer = $_POST['certification_issuer'];
        $issued_date = $_POST['certification_issued_date'];
        $credential_url = $_POST['certification_url'];
        
        $stmt = $conn->prepare("UPDATE certifications SET name=?, issuer=?, issued_date=?, credential_url=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $issuer, $issued_date, $credential_url, $cert_id);
        
        if ($stmt->execute()) {
            $success = "Certification updated successfully!";
        } else {
            $error = "Error updating certification!";
        }
        $stmt->close();
    }
    
    if (isset($_POST['delete_certification'])) {
        $cert_id = $_POST['certification_id'];
        
        $stmt = $conn->prepare("DELETE FROM certifications WHERE id=?");
        $stmt->bind_param("i", $cert_id);
        
        if ($stmt->execute()) {
            $success = "Certification deleted successfully!";
        } else {
            $error = "Error deleting certification!";
        }
        $stmt->close();
    }
}


function reorganizeProjects($conn) {
    
    $result = $conn->query("SELECT content_key, content_value FROM portfolio_content 
                           WHERE section_name = 'projects' AND content_key LIKE 'project%_title' 
                           ORDER BY content_key");
    
    $projects = [];
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
    
    
    $expected_num = 1;
    foreach ($projects as $project) {
        preg_match('/project(\d+)_title/', $project['content_key'], $matches);
        $current_num = $matches[1] ?? null;
        
        if ($current_num != $expected_num) {
            
            $old_prefix = "project{$current_num}_";
            $new_prefix = "project{$expected_num}_";
            
            // Update all keys for this project
            $types = ['title', 'desc', 'link', 'image'];
            foreach ($types as $type) {
                $old_key = $old_prefix . $type;
                $new_key = $new_prefix . $type;
                
                $stmt = $conn->prepare("UPDATE portfolio_content SET content_key = ? 
                                       WHERE section_name = 'projects' AND content_key = ?");
                $stmt->bind_param("ss", $new_key, $old_key);
                $stmt->execute();
                $stmt->close();
            }
        }
        
        $expected_num++;
    }
}

// this is will Get all current content in my portfolio_content
$content = [];
$result = $conn->query("SELECT * FROM portfolio_content");
while ($row = $result->fetch_assoc()) {
    $content[$row['section_name']][$row['content_key']] = $row['content_value'];
}

// for recent messages
$messages_result = $conn->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 10");
$recent_messages = [];
while ($row = $messages_result->fetch_assoc()) {
    $recent_messages[] = $row;
}

// for experience data
$experience_result = $conn->query("SELECT * FROM experience ORDER BY start_date DESC");
$experiences = [];
while ($row = $experience_result->fetch_assoc()) {
    $experiences[] = $row;
}

// for certifications data
$certifications_result = $conn->query("SELECT * FROM certifications ORDER BY issued_date DESC");
$certifications = [];
while ($row = $certifications_result->fetch_assoc()) {
    $certifications[] = $row;
}

//to Count projects correctly
$project_count = 0;
if (isset($content['projects'])) {
    foreach ($content['projects'] as $key => $value) {
        if (strpos($key, 'project') === 0 && strpos($key, '_title') !== false) {
            $project_count++;
        }
    }
}

//project
if ($project_count < 3) {
   
    if (!isset($content['projects']['project1_title'])) {
        $stmt = $conn->prepare("INSERT INTO portfolio_content (section_name, content_key, content_value) VALUES ('projects', 'project1_title', 'CSPC Intramural Sports Website') 
                               ON DUPLICATE KEY UPDATE content_value = 'CSPC Intramural Sports Website'");
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO portfolio_content (section_name, content_key, content_value) VALUES ('projects', 'project1_desc', 'A fully responsive intramural sports platform where users can view team standings, schedules, teams, and more.') 
                               ON DUPLICATE KEY UPDATE content_value = 'A fully responsive intramural sports platform where users can view team standings, schedules, teams, and more.'");
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO portfolio_content (section_name, content_key, content_value) VALUES ('projects', 'project1_link', 'https://yongsarte-cspc-intramural-web.vercel.app/') 
                               ON DUPLICATE KEY UPDATE content_value = 'https://yongsarte-cspc-intramural-web.vercel.app/'");
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO portfolio_content (section_name, content_key, content_value) VALUES ('projects', 'project1_image', 'asset/intrams.png') 
                               ON DUPLICATE KEY UPDATE content_value = 'asset/intrams.png'");
        $stmt->execute();
    }
    
    
    if (!isset($content['projects']['project2_title'])) {
        $stmt = $conn->prepare("INSERT INTO portfolio_content (section_name, content_key, content_value) VALUES ('projects', 'project2_title', 'Task Management App') 
                               ON DUPLICATE KEY UPDATE content_value = 'Task Management App'");
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO portfolio_content (section_name, content_key, content_value) VALUES ('projects', 'project2_desc', 'A productivity application for managing tasks with drag-and-drop functionality.') 
                               ON DUPLICATE KEY UPDATE content_value = 'A productivity application for managing tasks with drag-and-drop functionality.'");
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO portfolio_content (section_name, content_key, content_value) VALUES ('projects', 'project2_link', '#') 
                               ON DUPLICATE KEY UPDATE content_value = '#'");
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO portfolio_content (section_name, content_key, content_value) VALUES ('projects', 'project2_image', 'https://images.unsplash.com/photo-1552664730-d307ca884978') 
                               ON DUPLICATE KEY UPDATE content_value = 'https://images.unsplash.com/photo-1552664730-d307ca884978'");
        $stmt->execute();
    }
    
    
    if (!isset($content['projects']['project3_title'])) {
        $stmt = $conn->prepare("INSERT INTO portfolio_content (section_name, content_key, content_value) VALUES ('projects', 'project3_title', 'Weather Dashboard') 
                               ON DUPLICATE KEY UPDATE content_value = 'Weather Dashboard'");
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO portfolio_content (section_name, content_key, content_value) VALUES ('projects', 'project3_desc', 'Real-time weather application with 5-day forecast and location detection.') 
                               ON DUPLICATE KEY UPDATE content_value = 'Real-time weather application with 5-day forecast and location detection.'");
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO portfolio_content (section_name, content_key, content_value) VALUES ('projects', 'project3_link', '#') 
                               ON DUPLICATE KEY UPDATE content_value = '#'");
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO portfolio_content (section_name, content_key, content_value) VALUES ('projects', 'project3_image', 'https://images.unsplash.com/photo-1551288049-bebda4e38f71') 
                               ON DUPLICATE KEY UPDATE content_value = 'https://images.unsplash.com/photo-1551288049-bebda4e38f71'");
        $stmt->execute();
    }
    
    // Refresh content after adding default projects
    $content = [];
    $result = $conn->query("SELECT * FROM portfolio_content");
    while ($row = $result->fetch_assoc()) {
        $content[$row['section_name']][$row['content_key']] = $row['content_value'];
    }
    
    //  to Recount projects
    $project_count = 0;
    if (isset($content['projects'])) {
        foreach ($content['projects'] as $key => $value) {
            if (strpos($key, 'project') === 0 && strpos($key, '_title') !== false) {
                $project_count++;
            }
        }
    }
}

// for message count
$message_count = count($recent_messages);
$experience_count = count($experiences);
$certification_count = count($certifications);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Portfolio</title>
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-cogs"></i> Portfolio Admin Panel</h1>
            <form method="POST" action="admin_logout.php">
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <!-- Dashboard Cards -->
        <div class="dashboard">
            <div class="dashboard-card">
                <i class="fas fa-project-diagram"></i>
                <h3>Total Projects</h3>
                <p><?php echo $project_count; ?></p>
                <small>Showcasing your work</small>
            </div>
            
            <div class="dashboard-card">
                <i class="fas fa-comments"></i>
                <h3>Messages</h3>
                <p><?php echo $message_count; ?></p>
                <small>Contact inquiries</small>
            </div>
            
            <div class="dashboard-card">
                <i class="fas fa-briefcase"></i>
                <h3>Experience</h3>
                <p><?php echo $experience_count; ?></p>
                <small>Work experience</small>
            </div>
            
            <div class="dashboard-card">
                <i class="fas fa-certificate"></i>
                <h3>Certifications</h3>
                <p><?php echo $certification_count; ?></p>
                <small>Your credentials</small>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="content-section">
            <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
            <div class="quick-actions">
                <div class="action-card" onclick="scrollToSection('projects')">
                    <i class="fas fa-edit"></i>
                    <h4>Edit Projects</h4>
                    <p>Update your project details</p>
                </div>
                
                <div class="action-card" onclick="scrollToSection('experience')">
                    <i class="fas fa-briefcase"></i>
                    <h4>Manage Experience</h4>
                    <p>Update work history</p>
                </div>
                
                <div class="action-card" onclick="scrollToSection('certifications')">
                    <i class="fas fa-certificate"></i>
                    <h4>Manage Certifications</h4>
                    <p>Add credentials</p>
                </div>
            </div>
        </div>
        
        <div class="admin-sections">
            <div>
                <form method="POST">
                    <!-- About Section -->
                    <div class="content-section" id="about">
                        <h3><i class="fas fa-user"></i> About Section</h3>
                        
                        <div class="form-group">
                            <label><i class="fas fa-paragraph"></i> About Text</label>
                            <textarea name="content_about_text" placeholder="Enter about text"><?php echo $content['about']['about_text'] ?? ''; ?></textarea>
                            <input type="hidden" name="section_about_text" value="about">
                        </div>
                        
                        <div class="form-group">
    <label><i class="fas fa-calendar"></i> Education - Elementary (Year)</label>
    <input type="text" name="content_education_elementary_year" 
           value="<?php echo $content['about']['education_elementary_year'] ?? '2010 – 2016'; ?>">
    <input type="hidden" name="section_education_elementary_year" value="about">
</div>

<div class="form-group">
    <label><i class="fas fa-graduation-cap"></i> Education - Elementary (School)</label>
    <input type="text" name="content_education_elementary" 
           value="<?php echo $content['about']['education_elementary'] ?? 'Polangui South Central Elementary School (PSCES)'; ?>">
    <input type="hidden" name="section_education_elementary" value="about">
</div>


<div class="form-group">
    <label><i class="fas fa-calendar"></i> Education - Junior High (Year)</label>
    <input type="text" name="content_education_junior_year" 
           value="<?php echo $content['about']['education_junior_year'] ?? '2016 – 2020'; ?>">
    <input type="hidden" name="section_education_junior_year" value="about">
</div>

<div class="form-group">
    <label><i class="fas fa-graduation-cap"></i> Education - Junior High (School)</label>
    <input type="text" name="content_education_junior" 
           value="<?php echo $content['about']['education_junior'] ?? '(PGCHS) Polangui General Comprehensive High School'; ?>">
    <input type="hidden" name="section_education_junior" value="about">
</div>


<div class="form-group">
    <label><i class="fas fa-calendar"></i> Education - Senior High (Year)</label>
    <input type="text" name="content_education_senior_year" 
           value="<?php echo $content['about']['education_senior_year'] ?? '2020 – 2023'; ?>">
    <input type="hidden" name="section_education_senior_year" value="about">
</div>

<div class="form-group">
    <label><i class="fas fa-graduation-cap"></i> Education - Senior High (School)</label>
    <input type="text" name="content_education_senior" 
           value="<?php echo $content['about']['education_senior'] ?? '(PGCHS) Polangui General Comprehensive High School - GAS STRAND'; ?>">
    <input type="hidden" name="section_education_senior" value="about">
</div>


<div class="form-group">
    <label><i class="fas fa-calendar"></i> Education - College (Year)</label>
    <input type="text" name="content_education_college_year" 
           value="<?php echo $content['about']['education_college_year'] ?? '2022 – Present'; ?>">
    <input type="hidden" name="section_education_college_year" value="about">
</div>

<div class="form-group">
    <label><i class="fas fa-graduation-cap"></i> Education - College (School)</label>
    <input type="text" name="content_education_college" 
           value="<?php echo $content['about']['education_college'] ?? '(CSPC) Camarines Sur Polytechnic Colleges'; ?>">
    <input type="hidden" name="section_education_college" value="about">
</div>

                        
                        <div class="crud-buttons">
                            <button type="submit" name="update_content" class="submit-btn">
                                <i class="fas fa-save"></i> Save About Section
                            </button>
                        </div>
                    </div>
                </form>
                
                <!-- Projects Section -->
                <form method="POST">
                    <div class="content-section" id="projects">
                        <h3><i class="fas fa-project-diagram"></i> Projects Section (<?php echo $project_count; ?> projects)</h3>
                        
                       
                        <?php if (isset($content['projects']['project1_title'])): ?>
                            <div class="project-item">
                                <h4>Project 1: CSPC Intramural Sports Website</h4>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-heading"></i> Title</label>
                                    <input type="text" name="content_project1_title" value="<?php echo htmlspecialchars($content['projects']['project1_title'] ?? 'CSPC Intramural Sports Website'); ?>">
                                    <input type="hidden" name="section_project1_title" value="projects">
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-align-left"></i> Description</label>
                                    <textarea name="content_project1_desc"><?php echo htmlspecialchars($content['projects']['project1_desc'] ?? 'A fully responsive intramural sports platform where users can view team standings, schedules, teams, and more.'); ?></textarea>
                                    <input type="hidden" name="section_project1_desc" value="projects">
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-link"></i> Link</label>
                                    <input type="text" name="content_project1_link" value="<?php echo htmlspecialchars($content['projects']['project1_link'] ?? 'https://yongsarte-cspc-intramural-web.vercel.app/'); ?>">
                                    <input type="hidden" name="section_project1_link" value="projects">
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-image"></i> Image URL</label>
                                    <input type="text" name="content_project1_image" value="<?php echo htmlspecialchars($content['projects']['project1_image'] ?? 'asset/intrams.png'); ?>">
                                    <input type="hidden" name="section_project1_image" value="projects">
                                </div>
                                
                                <div class="crud-buttons">
                                    <button type="submit" name="update_content" class="btn-add">
                                        <i class="fas fa-save"></i> Update Project 1
                                    </button>
                                    <button type="submit" name="delete_project" class="btn-delete" onclick="return confirm('Are you sure you want to delete Project 1?')">
                                        <i class="fas fa-trash"></i> Delete Project 1
                                    </button>
                                    <input type="hidden" name="project_to_delete" value="1">
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- as of now this is template only -->
                        <?php if (isset($content['projects']['project2_title'])): ?>
                            <div class="project-item">
                                <h4>Project 2: Task Management App</h4>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-heading"></i> Title</label>
                                    <input type="text" name="content_project2_title" value="<?php echo htmlspecialchars($content['projects']['project2_title'] ?? 'Task Management App'); ?>">
                                    <input type="hidden" name="section_project2_title" value="projects">
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-align-left"></i> Description</label>
                                    <textarea name="content_project2_desc"><?php echo htmlspecialchars($content['projects']['project2_desc'] ?? 'A productivity application for managing tasks with drag-and-drop functionality.'); ?></textarea>
                                    <input type="hidden" name="section_project2_desc" value="projects">
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-link"></i> Link</label>
                                    <input type="text" name="content_project2_link" value="<?php echo htmlspecialchars($content['projects']['project2_link'] ?? '#'); ?>">
                                    <input type="hidden" name="section_project2_link" value="projects">
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-image"></i> Image URL</label>
                                    <input type="text" name="content_project2_image" value="<?php echo htmlspecialchars($content['projects']['project2_image'] ?? 'https://images.unsplash.com/photo-1552664730-d307ca884978'); ?>">
                                    <input type="hidden" name="section_project2_image" value="projects">
                                </div>
                                
                                <div class="crud-buttons">
                                    <button type="submit" name="update_content" class="btn-add">
                                        <i class="fas fa-save"></i> Update Project 2
                                    </button>
                                    <button type="submit" name="delete_project" class="btn-delete" onclick="return confirm('Are you sure you want to delete Project 2?')">
                                        <i class="fas fa-trash"></i> Delete Project 2
                                    </button>
                                    <input type="hidden" name="project_to_delete" value="2">
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- template -->
                        <?php if (isset($content['projects']['project3_title'])): ?>
                            <div class="project-item">
                                <h4>Project 3: Weather Dashboard</h4>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-heading"></i> Title</label>
                                    <input type="text" name="content_project3_title" value="<?php echo htmlspecialchars($content['projects']['project3_title'] ?? 'Weather Dashboard'); ?>">
                                    <input type="hidden" name="section_project3_title" value="projects">
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-align-left"></i> Description</label>
                                    <textarea name="content_project3_desc"><?php echo htmlspecialchars($content['projects']['project3_desc'] ?? 'Real-time weather application with 5-day forecast and location detection.'); ?></textarea>
                                    <input type="hidden" name="section_project3_desc" value="projects">
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-link"></i> Link</label>
                                    <input type="text" name="content_project3_link" value="<?php echo htmlspecialchars($content['projects']['project3_link'] ?? '#'); ?>">
                                    <input type="hidden" name="section_project3_link" value="projects">
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-image"></i> Image URL</label>
                                    <input type="text" name="content_project3_image" value="<?php echo htmlspecialchars($content['projects']['project3_image'] ?? 'https://images.unsplash.com/photo-1551288049-bebda4e38f71'); ?>">
                                    <input type="hidden" name="section_project3_image" value="projects">
                                </div>
                                
                                <div class="crud-buttons">
                                    <button type="submit" name="update_content" class="btn-add">
                                        <i class="fas fa-save"></i> Update Project 3
                                    </button>
                                    <button type="submit" name="delete_project" class="btn-delete" onclick="return confirm('Are you sure you want to delete Project 3?')">
                                        <i class="fas fa-trash"></i> Delete Project 3
                                    </button>
                                    <input type="hidden" name="project_to_delete" value="3">
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- about fixed projects -->
                        <div class="note-box">
                            <p><i class="fas fa-info-circle"></i> <strong>Note:</strong> These are the three fixed projects that match your portfolio website. You can edit their details but cannot add new projects.</p>
                        </div>
                    </div>
                </form>
                
                <!-- Experience Section - Each experience in separate form -->
                <div class="content-section" id="experience">
                    <h3><i class="fas fa-briefcase"></i> Experience Section (<?php echo $experience_count; ?> entries)</h3>
                    
                    
                    <?php foreach ($experiences as $exp): ?>
                        <form method="POST">
                            <div class="experience-item">
                                <div class="form-group">
                                    <label>Job Title</label>
                                    <input type="text" name="experience_title" value="<?php echo htmlspecialchars($exp['title']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="experience_description" required><?php echo htmlspecialchars($exp['description']); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" name="experience_start_date" value="<?php echo $exp['start_date']; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>End Date (Leave empty if current)</label>
                                    <input type="date" name="experience_end_date" value="<?php echo $exp['end_date']; ?>">
                                </div>
                                
                                <div class="crud-buttons">
                                    <button type="submit" name="update_experience" class="btn-add">
                                        <i class="fas fa-save"></i> Update Experience
                                    </button>
                                    <button type="submit" name="delete_experience" class="btn-delete" onclick="return confirm('Are you sure you want to delete this experience?')">
                                        <i class="fas fa-trash"></i> Delete Experience
                                    </button>
                                    <input type="hidden" name="experience_id" value="<?php echo $exp['id']; ?>">
                                </div>
                            </div>
                        </form>
                    <?php endforeach; ?>
                    
                    <!-- Add New Experience Form -->
                    <form method="POST">
                        <div class="add-experience-form">
                            <h4><i class="fas fa-plus-circle"></i> Add New Experience</h4>
                            
                            <div class="form-group">
                                <label>Job Title</label>
                                <input type="text" name="new_experience_title" placeholder="Enter job title" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="new_experience_description" placeholder="Enter job description" required></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="date" name="new_experience_start_date">
                            </div>
                            
                            <div class="form-group">
                                <label>End Date (Leave empty if current)</label>
                                <input type="date" name="new_experience_end_date">
                            </div>
                            
                            <div class="crud-buttons">
                                <button type="submit" name="add_experience" class="btn-add">
                                    <i class="fas fa-plus"></i> Add New Experience
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Certifications Section - Each certification in separate form -->
                <div class="content-section" id="certifications">
                    <h3><i class="fas fa-certificate"></i> Certifications Section (<?php echo $certification_count; ?> entries)</h3>
                    
                    
                    <?php foreach ($certifications as $cert): ?>
                        <form method="POST">
                            <div class="certification-item">
                                <div class="form-group">
                                    <label>Certification Name</label>
                                    <input type="text" name="certification_name" value="<?php echo htmlspecialchars($cert['name']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Issuer</label>
                                    <input type="text" name="certification_issuer" value="<?php echo htmlspecialchars($cert['issuer']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Issued Date</label>
                                    <input type="date" name="certification_issued_date" value="<?php echo $cert['issued_date']; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Credential URL (Optional)</label>
                                    <input type="url" name="certification_url" value="<?php echo htmlspecialchars($cert['credential_url']); ?>">
                                </div>
                                
                                <div class="crud-buttons">
                                    <button type="submit" name="update_certification" class="btn-add">
                                        <i class="fas fa-save"></i> Update Certification
                                    </button>
                                    <button type="submit" name="delete_certification" class="btn-delete" onclick="return confirm('Are you sure you want to delete this certification?')">
                                        <i class="fas fa-trash"></i> Delete Certification
                                    </button>
                                    <input type="hidden" name="certification_id" value="<?php echo $cert['id']; ?>">
                                </div>
                            </div>
                        </form>
                    <?php endforeach; ?>
                    
                    <!-- Add New Certification Form -->
                    <form method="POST">
                        <div class="add-certification-form">
                            <h4><i class="fas fa-plus-circle"></i> Add New Certification</h4>
                            
                            <div class="form-group">
                                <label>Certification Name</label>
                                <input type="text" name="new_certification_name" placeholder="Enter certification name" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Issuer</label>
                                <input type="text" name="new_certification_issuer" placeholder="Enter issuer name" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Issued Date</label>
                                <input type="date" name="new_certification_issued_date">
                            </div>
                            
                            <div class="form-group">
                                <label>Credential URL (Optional)</label>
                                <input type="url" name="new_certification_url" placeholder="Enter credential URL">
                            </div>
                            
                            <div class="crud-buttons">
                                <button type="submit" name="add_certification" class="btn-add">
                                    <i class="fas fa-plus"></i> Add New Certification
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Contact Section -->
                <form method="POST">
                    <div class="content-section" id="contact">
                        <h3><i class="fas fa-envelope"></i> Contact Section</h3>
                        
                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> Email</label>
                            <input type="text" name="content_contact_email" value="<?php echo $content['contact']['contact_email'] ?? 'josarte@my.cspc.edu.ph.com'; ?>">
                            <input type="hidden" name="section_contact_email" value="contact">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Phone</label>
                            <input type="text" name="content_contact_phone" value="<?php echo $content['contact']['contact_phone'] ?? '09947279813'; ?>">
                            <input type="hidden" name="section_contact_phone" value="contact">
                        </div>
                        
                        <div class="crud-buttons">
                            <button type="submit" name="update_content" class="submit-btn">
                                <i class="fas fa-save"></i> Save Contact Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Messages Section -->
            <div class="messages-section" id="messages">
                <h3><i class="fas fa-comments"></i> Recent Messages (<?php echo $message_count; ?>)</h3>
                
                <form method="POST">
                    <?php if (empty($recent_messages)): ?>
                        <p style="text-align: center; color: #ccc; padding: 20px;">
                            <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                            No messages yet.
                        </p>
                    <?php else: ?>
                        <?php foreach ($recent_messages as $message): ?>
                            <div class="message-item">
                                <button type="submit" name="delete_message" class="delete-message-btn" onclick="return confirm('Delete this message?')">
                                    <i class="fas fa-times"></i> Delete
                                </button>
                                <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                
                                <div class="message-meta">
                                    <strong><i class="fas fa-user"></i> <?php echo htmlspecialchars($message['name']); ?></strong>
                                    <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($message['email']); ?></span>
                                    <span><i class="fas fa-clock"></i> <?php echo $message['created_at']; ?></span>
                                </div>
                                <p><i class="fas fa-quote-left"></i> <?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <script>
        function scrollToSection(sectionId) {
            const targetElement = document.getElementById(sectionId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 20,
                    behavior: 'smooth'
                });
            }
        }
        
        // Confirm before deleting
        document.querySelectorAll('.btn-delete, .delete-message-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this item?')) {
                    e.preventDefault();
                }
            });
        });
    </script>

    
</body>
</html>
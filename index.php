<?php
include 'db.php';

// for content from database
$content = [];
$result = $conn->query("SELECT * FROM portfolio_content");
while ($row = $result->fetch_assoc()) {
    $content[$row['section_name']][$row['content_key']] = $row['content_value'];
}

// for experience data
$experiences = [];
$exp_result = $conn->query("SELECT * FROM experience ORDER BY start_date DESC");
while ($row = $exp_result->fetch_assoc()) {
    $experiences[] = $row;
}

// for certifications data
$certifications = [];
$cert_result = $conn->query("SELECT * FROM certifications ORDER BY issued_date DESC");
while ($row = $cert_result->fetch_assoc()) {
    $certifications[] = $row;
}


$default_content = [
    'about' => [
        'about_text' => "I'm a passionate web developer with expertise in creating dynamic and responsive websites. With a strong foundation in both front-end and back-end technologies, I strive to create engaging user experiences with clean and efficient code.",
        'education_elementary' => 'Elementary (2010 – 2016)<br>Polangui South Central Elementary School(PSCES)',
        'education_junior' => 'Junior High School (2016 – 2020)<br>(PGCHS) Polangui General Comprehensive High School',
        'education_senior' => 'Senior High School (2020 – 2023)<br>(PGCHS) Polangui General Comprehensive High School<br>GAS STRAND',
        'education_college' => 'College (2022 – Present)<br>(CSPC) Camarines Sur Polytechnic Colleges'
    ],
    'projects' => [
        'project1_title' => 'CSPC Intramural Sports Website',
        'project1_desc' => 'A fully responsive intramural sports platform where users can view team standings, schedules, teams, and more.',
        'project1_link' => 'https://yongsarte-cspc-intramural-web.vercel.app/',
        'project1_image' => 'asset/intrams.png',
        
    ],
    'contact' => [
        'contact_email' => 'josarte@my.cspc.edu.ph.com',
        'contact_phone' => '09947279813'
    ]
];

// Function to get content with fallback to defaults
function get_content($section, $key) {
    global $content, $default_content;
    return $content[$section][$key] ?? $default_content[$section][$key] ?? '';
}

// Function to format date
function format_date($date) {
    if (empty($date)) return '';
    return date('M Y', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>John Alexander Sarte | Portfolio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/devicon.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div id="header">
        <div class="particles"></div>
        <div class="container">
            <nav id="navbar">
                <div class="logo">yowyong</div>
                <ul id="sidemenu">
                    <li><a href="#header" class="nav-link active">Home</a></li>
                    <li><a href="#about" class="nav-link">About</a></li>
                    <li><a href="#projects" class="nav-link">Projects</a></li>
                    <li><a href="#skills" class="nav-link">Skills</a></li>
                    <li><a href="#contact" class="nav-link">Contact</a></li>
                    <li><a href="admin_login.php" class="nav-link" style="color: ;">Admin</a></li>
                </ul>
                <div class="mobile-menu">
                    <i class="fas fa-bars"></i>
                </div>
            </nav>

            <div class="header-content">
                <div class="header-text">
                    <p>Web Developer & Designer</p>
                    <h1>Hi, I'm <span>John Alexander</span><br>Sarte</h1>
                    <p class="typewriter" id="typewriter"></p>
                    <a href="#contact" class="btn nav-link">Contact Me</a>
                </div>
                <div class="header-image">
                    <img src="asset/image.png" alt="John Alexander Sarte">
                </div>
            </div>
        </div>
    </div>

    <!-- About Section -->
    <div id="about">
        <div class="container">
            <h2 class="section-title">About Me</h2>
            <div class="about-content">
                <div class="about-col-1">
                    <img src="asset/yow.jpg" alt="Profile Image">
                </div>
                <div class="about-col-2">
                    <p><?php echo get_content('about', 'about_text'); ?></p>

                    <div class="tab-titles">
                        <p class="tab-links active-link" onclick="opentab('education')">Education</p>
                        <p class="tab-links" onclick="opentab('experience')">Experience</p>
                        <p class="tab-links" onclick="opentab('certifications')">Certifications</p>
                    </div>

                  <!--education -->
                  <div class="tab-contents active-tab" id="education">
    <ul>
        <li>
            <span><?php echo get_content('about', 'education_elementary_year'); ?></span><br>
            <?php echo get_content('about', 'education_elementary'); ?>
        </li>
        <li>
            <span><?php echo get_content('about', 'education_junior_year'); ?></span><br>
            <?php echo get_content('about', 'education_junior'); ?>
        </li>
        <li>
            <span><?php echo get_content('about', 'education_senior_year'); ?></span><br>
            <?php echo get_content('about', 'education_senior'); ?>
        </li>
        <li>
            <span><?php echo get_content('about', 'education_college_year'); ?></span><br>
            <?php echo get_content('about', 'education_college'); ?>
        </li>
    </ul>
</div>


                     <!--experience -->
                    <div class="tab-contents" id="experience">
                        <ul>
                            <?php if (empty($experiences)): ?>
                                <li><span>No experience yet</span><br>Currently building my portfolio</li>
                            <?php else: ?>
                                <?php foreach ($experiences as $exp): ?>
                                    <li>
                                        <span><?php echo htmlspecialchars($exp['title']); ?></span><br>
                                        <?php echo htmlspecialchars($exp['description']); ?>
                                        <?php if ($exp['start_date']): ?>
                                            <br><small>
                                                <?php echo format_date($exp['start_date']); ?> - 
                                                <?php echo $exp['end_date'] ? format_date($exp['end_date']) : 'Present'; ?>
                                            </small>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                      <!--certifications -->
                    <div class="tab-contents" id="certifications">
                        <ul>
                            <?php if (empty($certifications)): ?>
                                <li><span>Web Development Certification</span><br>Online Course Completion</li>
                            <?php else: ?>
                                <?php foreach ($certifications as $cert): ?>
                                    <li>
                                        <span><?php echo htmlspecialchars($cert['name']); ?></span><br>
                                        <?php echo htmlspecialchars($cert['issuer']); ?>
                                        <?php if ($cert['issued_date']): ?>
                                            <br><small>Issued: <?php echo format_date($cert['issued_date']); ?></small>
                                        <?php endif; ?>
                                        <?php if ($cert['credential_url']): ?>
                                            <br><a href="<?php echo htmlspecialchars($cert['credential_url']); ?>" target="_blank" style="color: #ff004f; text-decoration: none;">View Credential</a>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Section -->
<div id="projects">
    <div class="container">
        <h2 class="section-title">My Projects</h2>
        <div class="project-list">
            <div class="project">
                <img src="<?php echo get_content('projects', 'project1_image'); ?>" alt="CSPC Intramurals Website">
                <div class="project-content">
                    <h3><?php echo get_content('projects', 'project1_title'); ?></h3>
                    <p><?php echo get_content('projects', 'project1_desc'); ?></p>
                </div>
                <div class="layer">
                    <h3><?php echo get_content('projects', 'project1_title'); ?></h3>
                    <p><?php echo get_content('projects', 'project1_desc'); ?></p>
                    <a href="<?php echo get_content('projects', 'project1_link'); ?>" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
            </div>
            <div class="project">
                <img src="<?php echo get_content('projects', 'project2_image'); ?>" alt="Task Management App">
                <div class="project-content">
                    <h3><?php echo get_content('projects', 'project2_title'); ?></h3>
                    <p><?php echo get_content('projects', 'project2_desc'); ?></p>
                </div>
                <div class="layer">
                    <h3><?php echo get_content('projects', 'project2_title'); ?></h3>
                    <p><?php echo get_content('projects', 'project2_desc'); ?></p>
                    <a href="<?php echo get_content('projects', 'project2_link'); ?>" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
            </div>
            <div class="project">
                <img src="<?php echo get_content('projects', 'project3_image'); ?>" alt="Weather Dashboard">
                <div class="project-content">
                    <h3><?php echo get_content('projects', 'project3_title'); ?></h3>
                    <p><?php echo get_content('projects', 'project3_desc'); ?></p>
                </div>
                <div class="layer">
                    <h3><?php echo get_content('projects', 'project3_title'); ?></h3>
                    <p><?php echo get_content('projects', 'project3_desc'); ?></p>
                    <a href="<?php echo get_content('projects', 'project3_link'); ?>" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
                
            </div>
        </div>
    </div>
</div>

    <!-- Skills  -->
    <div id="skills">
        <div class="container">
            <h2 class="section-title">My Skills</h2>
            <div class="skills-container">
                <div class="skills-list">
                    <div class="skill">
                        <i class="fab fa-html5"></i>
                        <h3>HTML5</h3>
                        <p>Semantic markup, accessibility, and modern HTML features.</p>
                    </div>
                    <div class="skill">
                        <i class="fab fa-css3-alt"></i>
                        <h3>CSS3</h3>
                        <p>Flexbox, Grid, animations, and responsive design principles.</p>
                    </div>
                    <div class="skill">
                        <i class="fab fa-js"></i>
                        <h3>JavaScript</h3>
                        <p>ES6+, DOM manipulation, async programming, and frameworks.</p>
                    </div>
                    <div class="skill">
                        <i class="devicon-mysql-plain colored"></i>
                        <h3>MySQL</h3>
                        <p>Database management.</p>
                    </div>
                    <div class="skill">
                        <i class="fab fa-php"></i>
                        <h3>PHP</h3>
                        <p>Component-based architecture, hooks, and state management.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Section-->
    <div id="contact">
        <div class="container">
            <h2 class="section-title">Contact Me</h2>
            <div class="about-content">
                <div class="contact-left">
                    <p><i class="fas fa-paper-plane"></i> <?php echo get_content('contact', 'contact_email'); ?></p>
                    <p><i class="fas fa-phone-square-alt"></i><?php echo get_content('contact', 'contact_phone'); ?></p>
                    <div class="social-icons">
                        <a href="https://www.facebook.com/john.alexander.sarte.2024/about"><i class="fab fa-facebook"></i></a>
                        <a href="https://www.instagram.com/johnalexandersarte/"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.tiktok.com/@johnalexandersart"><i class="fab fa-tiktok"></i></a>
                    </div>
                    <a href="" class="btn btn2">Download CV</a>
                </div>
                <div class="contact-right">
                    <form id="contactForm" method="POST" action="process_form.php">
                        <input type="text" name="Name" placeholder="Your Name" required>
                        <input type="email" name="Email" placeholder="Your Email" required>
                        <textarea name="Message" rows="6" placeholder="Your Message" required></textarea>
                        <button type="submit" class="btn btn2">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="copyright">
        <p>John Alexander Sarte Portfolio</p>
    </div>

    <script src="script.js"></script>
</body>
</html>
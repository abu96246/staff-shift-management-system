 </div><!-- End main-wrapper -->

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-grid">
                <!-- Company Info -->
                <div class="footer-section">
                    <div class="footer-logo">
                     <img src="assets/img/logo.png" alt="Railway Logo" class="img-fluid" style="max-width: 100px;">
                     <!--   <div class="footer-logo-icon"></div>-->
                        <div class="footer-logo-text">
                            <p>Operation Workshop Shift Management System</p>
                        </div>
                    </div>
                    <p class="footer-description">
                        Connecting Business Ethiopia and Djibouti through efficient railway operations, 
                        ensuring safe and reliable transportation services across the region.
                    </p>
                    <div class="footer-stats">
                        <div class="stat-item">
                            <span class="stat-number">756km</span>
                            <span class="stat-label">Track</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">8/6</span>
                            <span class="stat-label">Operations</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">98.7%</span>
                            <span class="stat-label">Reliability</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="staff.php"><i class="fas fa-users"></i> Staff Management</a></li>
                        <li><a href="assignments.php"><i class="fas fa-calendar-alt"></i> Shift Assignments</a></li>
                        <li><a href="schedule.php"><i class="fas fa-calendar"></i> Schedule</a></li>
                        <li><a href="swap_requests.php"><i class="fas fa-exchange-alt"></i> Shift Swap</a></li>
                        <li><a href="availability.php"><i class="fas fa-check-circle"></i> Availability</a></li>
                        <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                   
                    </ul>
                </div>

                <!-- Operations -->
               

                <!-- Contact & System Info -->
                <div class="footer-section">
                    <h4>Contact Information</h4>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <strong>Headquarters</strong>
                                <p>Addis Ababa, Furi Ethiopia</p>
                            </div>
                        </div>
                    
                        <div class="contact-item">
                            <i class="fas fa-search"></i>
                            <div>
                                <strong>Operation Website</strong>
                                <p>www.edrsc.com</p>
                            </div>
                            </div>
                            <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>Call Center</strong>
                                <p>9546</p>
                            </div>
                            </div>
                    </div>
                    
                    <div class="system-status">
                        <div class="status-indicator">
                            <div class="status-dot active"></div>
                            <span>System Operational</span>
                        </div>
                        <div class="last-updated">
                            Last updated: <?php echo date('M d, Y H:i'); ?> 
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <div class="copyright">
                        <p>&copy; <?php echo date('Y'); ?> Ethiopia-Djibouti Railway. All rights reserved.</p>
                     
                       <small>Powered by Abubeker Mukemil | </small>
                    </div>
        
                    <div class="social-links">
                      
                        <a href="#" title="LinkedIn"><i class="fab fa-linkedin"></i></a>
                        <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" title="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <style>
        .main-wrapper {
            min-height: calc(100vh - 200px);
            padding-bottom: 2rem;
        }
        
        .footer {
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
            color: #f9fafb;
            margin-top: auto;
        }
        
        .footer-content {
            max-width: 900px;
            margin: 0 auto;
            padding: 3rem 2rem 0;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 2rem;
            margin-bottom: 1rem;
        }
        
        .footer-section h4 {
            color: #60a5fa;
            font-size: 1.125rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #374151;
            padding-bottom: 0.5rem;
        }
        
        .footer-logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .footer-logo-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            color: white;
        }
        
        .footer-logo-text h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
            color: #f9fafb;
        }
        
        .footer-logo-text p {
            font-size: 0.875rem;
            color: #9ca3af;
            margin: 0;
        }
        
        .footer-description {
            color: #d1d5db;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        
        .footer-stats {
            display: flex;
            gap: 2rem;
            margin-bottom: 1rem;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: #60a5fa;
        }
        
        .stat-label {
            font-size: 0.75rem;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-links li {
            margin-bottom: 0.75rem;
        }
        
        .footer-links a {
            color: #d1d5db;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            padding: 0.25rem 0;
        }
        
        .footer-links a:hover {
            color: #60a5fa;
            transform: translateX(4px);
        }
        
        .footer-links i {
            width: 16px;
            color: #6b7280;
        }
        
        .contact-info {
            margin-bottom: 2rem;
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .contact-item i {
            color: #60a5fa;
            font-size: 1.125rem;
            margin-top: 0.25rem;
            width: 20px;
        }
        
        .contact-item strong {
            color: #f9fafb;
            display: block;
            margin-bottom: 0.25rem;
        }
        
        .contact-item p {
            color: #d1d5db;
            margin: 0;
            line-height: 1.4;
        }
        
        .system-status {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 8px;
            padding: 1rem;
        }
        
        .status-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            animation: pulse 2s infinite;
        }
        
        .status-dot.active {
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.3);
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        
        .last-updated {
            font-size: 0.75rem;
            color: #9ca3af;
        }
        
        .footer-bottom {
            border-top: 1px solid #374151;
            padding: 1rem 0;
        }
        
        .footer-bottom-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .copyright p {
            margin: 0;
            color: #9ca3af;
            font-size: 0.875rem;
        }
        
        .footer-links-bottom {
            display: flex;
            gap: 1rem;
        }
        
        .footer-links-bottom a {
            color: #d1d5db;
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.3s ease;
        }
        
        .footer-links-bottom a:hover {
            color: #60a5fa;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
        }
        
        .social-links a {
            width: 40px;
            height: 40px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #60a5fa;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: rgba(59, 130, 246, 0.2);
            transform: translateY(-2px);
        }
        
        @media (max-width: 1024px) {
            .footer-grid {
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            .footer-content {
                padding: 2rem 1rem 0;
            }
            
            .footer-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .footer-stats {
                justify-content: space-around;
            }
            
            .footer-bottom-content {
                flex-direction: column;
                text-align: center;
                gap: 1.5rem;
            }
            
            .footer-links-bottom {
                flex-wrap: wrap;
                justify-content: center;
                gap: 1rem;
            }
        }
        
        @media (max-width: 480px) {
            .footer-stats {
                flex-direction: column;
                gap: 1rem;
            }
            
            .contact-item {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>

    
</body>
</html>
-- Migration: Create classifications table with category + sub_item structure
-- Based on DICT Annex A.1.1 - List of ICT Equipment, Goods, Support Services and Consulting Services

CREATE TABLE IF NOT EXISTS `classifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(255) NOT NULL,
  `sub_item` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `date_created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 1. Computer Systems
INSERT INTO `classifications` (`category`, `sub_item`, `status`) VALUES
('Computer Systems', 'Central Processing Unit', 'active'),
('Computer Systems', 'Desktop PCs', 'active'),
('Computer Systems', 'Portable/Laptop Computer', 'active'),
('Computer Systems', 'Tablet PC/Handheld and Pocket PC/PDA/Smart Phone', 'active'),
('Computer Systems', 'Server/Workstation/Minicomputer', 'active'),
('Computer Systems', 'Mainframe', 'active');

-- 2. Peripherals
INSERT INTO `classifications` (`category`, `sub_item`, `status`) VALUES
('Peripherals', 'Digital Camera (CDD Camera)', 'active'),
('Peripherals', 'Film Processor', 'active'),
('Peripherals', 'Hardware Lock', 'active'),
('Peripherals', 'Monitor', 'active'),
('Peripherals', 'VGA Cards/Boards', 'active');

-- 3. Storage Devices
INSERT INTO `classifications` (`category`, `sub_item`, `status`) VALUES
('Storage Devices', 'CD Recorder', 'active'),
('Storage Devices', 'CD Tower', 'active'),
('Storage Devices', 'CD Writer', 'active'),
('Storage Devices', 'CD-ROM Drive/Disk', 'active'),
('Storage Devices', 'Compact Flash Card', 'active'),
('Storage Devices', 'Computer Tape/Cartridge', 'active'),
('Storage Devices', 'Floppy Drive', 'active'),
('Storage Devices', 'Hard Disk Drive (Internal/External/Portable)', 'active'),
('Storage Devices', 'JAZ Drive', 'active'),
('Storage Devices', 'Magneto-Optical Disk Drive', 'active'),
('Storage Devices', 'Memory Stick (Standard/PRO/Duo/PRO Duo/Micro)', 'active'),
('Storage Devices', 'Multimedia Card (MMC)', 'active'),
('Storage Devices', 'Removable Storage Disk', 'active'),
('Storage Devices', 'SCSI Drive Module', 'active'),
('Storage Devices', 'Secure Digital Card (SD/miniSD/microSD/SDHC/SDXC)', 'active'),
('Storage Devices', 'SmartMedia Card', 'active'),
('Storage Devices', 'Solid State Drive (SSD)', 'active'),
('Storage Devices', 'Tape Backup/Tape Drive', 'active'),
('Storage Devices', 'Zip Drive', 'active'),
('Storage Devices', 'Storage Area Network (SAN)/Network Attached Storage (NAS)', 'active'),
('Storage Devices', 'USB Flash Drive', 'active');

-- 4. Printers
INSERT INTO `classifications` (`category`, `sub_item`, `status`) VALUES
('Printers', 'Band Printer', 'active'),
('Printers', 'Barcode/POS Printer', 'active'),
('Printers', 'Billboard Printer', 'active'),
('Printers', 'Design Jet Printer', 'active'),
('Printers', 'Digital Copy Printer', 'active'),
('Printers', 'Dot Matrix Printer', 'active'),
('Printers', 'Film Printer', 'active'),
('Printers', 'Impact Printer', 'active'),
('Printers', 'Ink Jet/Bubble Jet Printer', 'active'),
('Printers', 'Large Format Printer', 'active'),
('Printers', 'Laser Printer', 'active'),
('Printers', 'LED Printer', 'active'),
('Printers', 'Line Printer', 'active'),
('Printers', 'Passbook Printer', 'active'),
('Printers', 'Plotter', 'active'),
('Printers', 'POS Printer', 'active'),
('Printers', 'Poster Printer', 'active'),
('Printers', 'Printer Server', 'active'),
('Printers', 'Serial Printer', 'active'),
('Printers', 'Signmaker Printer', 'active'),
('Printers', 'Sticker Marker Printer', 'active'),
('Printers', 'Thermal Printer', 'active');

-- 5. Cards
INSERT INTO `classifications` (`category`, `sub_item`, `status`) VALUES
('Cards', 'Controller Card', 'active'),
('Cards', 'Enhancement Card', 'active'),
('Cards', 'Expansion Card', 'active'),
('Cards', 'Video/Graphics Card', 'active');

-- 6. Multimedia
INSERT INTO `classifications` (`category`, `sub_item`, `status`) VALUES
('Multimedia', 'Headphone/Earphone', 'active'),
('Multimedia', 'Multimedia Kit', 'active'),
('Multimedia', 'Multimedia Projector System', 'active'),
('Multimedia', 'Multimedia Speaker System', 'active'),
('Multimedia', 'Multimedia Storage System', 'active'),
('Multimedia', 'Other Multimedia Products', 'active');

-- 7. Input/Output Device
INSERT INTO `classifications` (`category`, `sub_item`, `status`) VALUES
('Input/Output Device', 'Barcode Reader', 'active'),
('Input/Output Device', 'CCTV/Video Camera', 'active'),
('Input/Output Device', 'Digital Copier', 'active'),
('Input/Output Device', 'Digitizer', 'active'),
('Input/Output Device', 'Facial Scanner', 'active'),
('Input/Output Device', 'Fingerprint Scanner', 'active'),
('Input/Output Device', 'Image Scanner', 'active'),
('Input/Output Device', 'Iris Scanner', 'active'),
('Input/Output Device', 'Keyboard', 'active'),
('Input/Output Device', 'Light Pen', 'active'),
('Input/Output Device', 'Microphone', 'active'),
('Input/Output Device', 'MIDI Keyboard/Other Digital Musical Instruments', 'active'),
('Input/Output Device', 'Motion Sensor', 'active'),
('Input/Output Device', 'Mouse', 'active'),
('Input/Output Device', 'Palm Scanner', 'active'),
('Input/Output Device', 'RFID Reader', 'active'),
('Input/Output Device', '3D Scanner', 'active'),
('Input/Output Device', 'Touch Screen', 'active'),
('Input/Output Device', 'Web Camera', 'active');

-- 8. Power Protection System
INSERT INTO `classifications` (`category`, `sub_item`, `status`) VALUES
('Power Protection System', 'Automatic Voltage Regulator', 'active'),
('Power Protection System', 'Battery Charger', 'active'),
('Power Protection System', 'Emergency Power Unit', 'active'),
('Power Protection System', 'Programmable/Portable Data Collector', 'active'),
('Power Protection System', 'Surge Protector/Suppressor', 'active'),
('Power Protection System', 'Uninterruptible Power Supply System', 'active');

-- 9. System Memory Boards
INSERT INTO `classifications` (`category`, `sub_item`, `status`) VALUES
('System Memory Boards', 'Math Coprocessor', 'active'),
('System Memory Boards', 'Memory Card', 'active'),
('System Memory Boards', 'Memory Module', 'active'),
('System Memory Boards', 'Memory Upgrades', 'active'),
('System Memory Boards', 'SIMM/DIMM RAM', 'active'),
('System Memory Boards', 'SDRAM/RDRAM/DDR3/DDR SDRAM', 'active');

-- 10. Data Communications and Networking Equipment
INSERT INTO `classifications` (`category`, `sub_item`, `status`) VALUES
('Data Communications and Networking Equipment', 'Adapters', 'active'),
('Data Communications and Networking Equipment', 'Bridges', 'active'),
('Data Communications and Networking Equipment', 'Echo Canceller', 'active'),
('Data Communications and Networking Equipment', 'Fax Machines', 'active'),
('Data Communications and Networking Equipment', 'Fax Modem/Card', 'active'),
('Data Communications and Networking Equipment', 'Microwave Repeater System', 'active'),
('Data Communications and Networking Equipment', 'Modem', 'active'),
('Data Communications and Networking Equipment', 'Multimedia Messaging System', 'active'),
('Data Communications and Networking Equipment', 'Multiplexers', 'active'),
('Data Communications and Networking Equipment', 'Network Desktop', 'active'),
('Data Communications and Networking Equipment', 'Network Interface Cards', 'active'),
('Data Communications and Networking Equipment', 'Network PC Cards', 'active'),
('Data Communications and Networking Equipment', 'Network Servers', 'active'),
('Data Communications and Networking Equipment', 'Network Tools', 'active'),
('Data Communications and Networking Equipment', 'PABX/Telephone System/Cellular Phones', 'active'),
('Data Communications and Networking Equipment', 'Paging Systems', 'active'),
('Data Communications and Networking Equipment', 'PC Telex Interface Cards', 'active'),
('Data Communications and Networking Equipment', 'Remote Access Server', 'active'),
('Data Communications and Networking Equipment', 'Routers', 'active'),
('Data Communications and Networking Equipment', 'Repeater', 'active'),
('Data Communications and Networking Equipment', 'Signaling Converter', 'active'),
('Data Communications and Networking Equipment', 'Structured Cabling', 'active'),
('Data Communications and Networking Equipment', 'Switches and Hubs', 'active'),
('Data Communications and Networking Equipment', 'Switching System', 'active'),
('Data Communications and Networking Equipment', 'Telephone Adaptor', 'active'),
('Data Communications and Networking Equipment', 'Telephone Remote Control Power Controller', 'active'),
('Data Communications and Networking Equipment', 'Test and Measurement System', 'active'),
('Data Communications and Networking Equipment', 'Transceiver', 'active'),
('Data Communications and Networking Equipment', 'Trunked Mobile Radio', 'active'),
('Data Communications and Networking Equipment', 'Video Conferencing System/Kit', 'active'),
('Data Communications and Networking Equipment', 'Voice Mail/Voice Messaging System', 'active'),
('Data Communications and Networking Equipment', 'Wires and Cabling System', 'active');

-- 11. ICT Support Services
INSERT INTO `classifications` (`category`, `sub_item`, `status`) VALUES
('ICT Support Services', 'Application Service Subscription', 'active'),
('ICT Support Services', 'Cloud Computing Services', 'active'),
('ICT Support Services', 'Computer Facilities Management', 'active'),
('ICT Support Services', 'Computer Hardware Servicing, Repair and Maintenance', 'active'),
('ICT Support Services', 'Contact Center Services', 'active'),
('ICT Support Services', 'Contingency Planning and Disaster Recovery Support Services', 'active'),
('ICT Support Services', 'Data Conversion/Encoding', 'active'),
('ICT Support Services', 'Data Processing Services', 'active'),
('ICT Support Services', 'Hardware, Software and Network Evaluation', 'active'),
('ICT Support Services', 'Hosting and ICT Infrastructure Provisioning Services (IaaS)', 'active'),
('ICT Support Services', 'ICT Backup Storage Services', 'active'),
('ICT Support Services', 'Infrastructure as a Service (IaaS)', 'active'),
('ICT Support Services', 'Internet Service Provider', 'active'),
('ICT Support Services', 'Multimedia and Graphics Design', 'active'),
('ICT Support Services', 'Network Penetration Testing', 'active'),
('ICT Support Services', 'Platform-as-a-Service (PaaS)', 'active'),
('ICT Support Services', 'PKI Subscription Services', 'active'),
('ICT Support Services', 'Risk/Vulnerability Assessment', 'active'),
('ICT Support Services', 'Software-as-a-Service (SaaS)', 'active'),
('ICT Support Services', 'Telecommunication Infrastructure Design, Installation and Testing', 'active'),
('ICT Support Services', 'Total ICT Systems Solution Integration', 'active');

-- 12. ICT Consulting Services
INSERT INTO `classifications` (`category`, `sub_item`, `status`) VALUES
('ICT Consulting Services', 'Application Programming', 'active'),
('ICT Consulting Services', 'Application Source Code Review', 'active'),
('ICT Consulting Services', 'Application Systems Customization and Maintenance', 'active'),
('ICT Consulting Services', 'Applications Systems Design and Development', 'active'),
('ICT Consulting Services', 'Business Continuity Planning', 'active'),
('ICT Consulting Services', 'Business Process Management (BPM)', 'active'),
('ICT Consulting Services', 'Business Process Reengineering (BPR)', 'active'),
('ICT Consulting Services', 'Change Management Plan Design', 'active'),
('ICT Consulting Services', 'Communication Plan Design', 'active'),
('ICT Consulting Services', 'E-Governance Audit', 'active'),
('ICT Consulting Services', 'Enterprise Architecture Formulation', 'active'),
('ICT Consulting Services', 'Formulation of Terms of Reference', 'active'),
('ICT Consulting Services', 'ICT Capacity Planning', 'active'),
('ICT Consulting Services', 'ICT Competency Plan Development', 'active'),
('ICT Consulting Services', 'ICT Contract Management', 'active'),
('ICT Consulting Services', 'ICT Course Design and Development', 'active'),
('ICT Consulting Services', 'ICT Infrastructure and Network Management Services', 'active'),
('ICT Consulting Services', 'ICT Infrastructure Library Management', 'active'),
('ICT Consulting Services', 'ICT Management Auditing', 'active'),
('ICT Consulting Services', 'ICT Organizational Design', 'active'),
('ICT Consulting Services', 'ICT Policy and Standards Design', 'active'),
('ICT Consulting Services', 'ICT Procurement Management', 'active'),
('ICT Consulting Services', 'ICT Project Feasibility Study', 'active'),
('ICT Consulting Services', 'ICT Project Management', 'active'),
('ICT Consulting Services', 'ICT Quality Management System Assessment', 'active'),
('ICT Consulting Services', 'ICT Recruitment and Placement', 'active'),
('ICT Consulting Services', 'ICT Security Audit', 'active'),
('ICT Consulting Services', 'ICT Solutions Engineering', 'active'),
('ICT Consulting Services', 'ICT Training Needs Analysis', 'active'),
('ICT Consulting Services', 'Information System Auditing', 'active'),
('ICT Consulting Services', 'Information Systems Strategic Plan Evaluation', 'active'),
('ICT Consulting Services', 'Information Systems Strategic Plan Formulation', 'active'),
('ICT Consulting Services', 'Network Systems Design, Development, Installation and Testing', 'active'),
('ICT Consulting Services', 'Organizational Knowledge Design and Development', 'active'),
('ICT Consulting Services', 'Request for Proposal Formulation (RFP)', 'active'),
('ICT Consulting Services', 'Risk Assessment and Evaluation', 'active'),
('ICT Consulting Services', 'Website Design', 'active'),
('ICT Consulting Services', 'Web Hosting', 'active'),
('ICT Consulting Services', 'Web-Based Programming', 'active');

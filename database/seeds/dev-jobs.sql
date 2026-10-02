-- DEVELOPMENT SAMPLE DATA ONLY — fictional postings so the Careers pages can be seen and tested locally.
-- Applied by scripts/migrate.php only when SEED_DEV_DATA=true (set in compose.yaml, never in production).
-- Re-runnable: INSERT IGNORE on the unique job_url.
INSERT IGNORE INTO jobs (job_url, job_name, job_company, job_location, job_logo, job_salary, job_type, job_lead, job_desc, job_responsibilities, job_skills) VALUES
('frontend-developer', 'Frontend Developer', 'ΛΞV - HR', 'Prague, Czech Republic', 'job_icon.png', '2 500 – 3 500', 'Freelance', 'Sample Lead',
 'Sample posting. Build fast, accessible interfaces for studio clients and keep the design system consistent.',
 'Turn designs into pixel-accurate pages. Review pull requests. Keep performance budgets green.',
 'Strong HTML, CSS and JavaScript. Experience with responsive layouts. Care for accessibility.'),
('ux-ui-designer', 'UX/UI Designer', 'ΛΞV - HR', 'Remote', 'job_icon.png', '2 000 – 3 000', 'Freelance', 'Sample Lead',
 'Sample posting. Shape product experiences from first sketch to final hand-off.',
 'Research and prototype flows. Maintain the component library. Present work to clients.',
 'Figma fluency. A portfolio of shipped interfaces. Clear written communication.'),
('php-developer', 'PHP Developer', 'NXR.EX', 'Prague, Czech Republic', 'job_icon.png', '3 000 – 4 000', 'Full-time', 'Sample Lead',
 'Sample posting. Work on the PHP back end behind the studio sites.',
 'Design small, well-tested features. Write safe, parameterised queries. Document what you build.',
 'Modern PHP (8.x). SQL. Understanding of web security basics.');

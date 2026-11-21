const SAFE_PROTOCOLS = new Set(['http:', 'https:']);

export function normalizeExternalUrl(rawUrl) {
  const value = String(rawUrl || '').trim();
  if (!value) return null;

  const withProtocol = /^[a-zA-Z][a-zA-Z\d+\-.]*:/.test(value) ? value : `https://${value}`;
  try {
    const parsed = new URL(withProtocol);
    return SAFE_PROTOCOLS.has(parsed.protocol) ? parsed.toString() : null;
  } catch {
    return null;
  }
}

const FLAGSHIP_COURSE_URLS = [
  { match: (t) => t.includes('cs50'), url: 'https://cs50.harvard.edu/x/' },
  { match: (t) => t.includes('odin project'), url: 'https://www.theodinproject.com/paths' },
  { match: (t) => t.includes('full stack open'), url: 'https://fullstackopen.com/en/' },
  { match: (t) => t.includes('javascript.info'), url: 'https://javascript.info/' },
];

const PLATFORM_RESOLVERS = [
  { keys: ['coursera'], url: (q) => `https://www.coursera.org/search?query=${q}` },
  { keys: ['udemy'], url: (q) => `https://www.udemy.com/courses/search/?q=${q}` },
  { keys: ['edx'], url: (q) => `https://www.edx.org/search?q=${q}` },
  { keys: ['freecodecamp'], url: () => 'https://www.freecodecamp.org/learn' },
  { keys: ['odin project'], url: () => 'https://www.theodinproject.com/paths' },
  { keys: ['university of helsinki', 'mooc.fi'], url: () => 'https://www.mooc.fi/en/' },
  { keys: ['udacity'], url: (q) => `https://www.udacity.com/catalog?search=${q}` },
  { keys: ['skillshare'], url: (q) => `https://www.skillshare.com/en/search?query=${q}` },
  { keys: ['interaction design'], url: () => 'https://www.interaction-design.org/courses' },
  { keys: ['linkedin learning'], url: (q) => `https://www.linkedin.com/learning/search?keywords=${q}` },
  { keys: ['microsoft learn'], url: (q) => `https://learn.microsoft.com/en-us/training/browse/?terms=${q}` },
  { keys: ['hubspot'], url: () => 'https://academy.hubspot.com/courses' },
  { keys: ['simplilearn'], url: (q) => `https://www.simplilearn.com/search?query=${q}` },
  { keys: ['amplitude'], url: () => 'https://academy.amplitude.com/' },
  { keys: ['product school'], url: () => 'https://productschool.com/' },
  { keys: ['cfi academy', 'corporate finance institute'], url: () => 'https://corporatefinanceinstitute.com/courses/' },
  { keys: ['stanford online'], url: (q) => `https://online.stanford.edu/explore?keywords=${q}` },
  { keys: ['mit opencourseware', 'mit web'], url: (q) => `https://ocw.mit.edu/search/?q=${q}` },
  { keys: ['kaggle'], url: () => 'https://www.kaggle.com/learn' },
  { keys: ['google cloud'], url: (q) => `https://www.cloudskillsboost.google/catalog?keywords=${q}` },
  { keys: ['google/android', 'android developers'], url: () => 'https://developer.android.com/courses' },
  { keys: ['google for developers'], url: () => 'https://developers.google.com/learn' },
  { keys: ['professor messer'], url: () => 'https://www.professormesser.com/' },
  { keys: ['tryhackme'], url: () => 'https://tryhackme.com/paths' },
  { keys: ['a cloud guru'], url: () => 'https://www.pluralsight.com/cloud-guru' },
  { keys: ['hashicorp'], url: () => 'https://developer.hashicorp.com/tutorials' },
  { keys: ['kodekloud'], url: () => 'https://kodekloud.com/courses/' },
  { keys: ['github'], url: () => 'https://skills.github.com/' },
  { keys: ['unity learn'], url: () => 'https://learn.unity.com/' },
  { keys: ['unreal'], url: () => 'https://dev.epicgames.com/community/unreal-engine/learning' },
  { keys: ['scrum alliance'], url: () => 'https://www.scrumalliance.org/' },
  { keys: ['scrum.org'], url: () => 'https://www.scrum.org/courses' },
  { keys: ['pmi'], url: () => 'https://www.pmi.org/certifications' },
  { keys: ['wall street prep'], url: () => 'https://www.wallstreetprep.com/' },
  { keys: ['mckinsey'], url: () => 'https://www.mckinsey.com/featured-insights' },
  { keys: ['biws'], url: () => 'https://breakingintowallstreet.com/' },
  { keys: ['becker'], url: () => 'https://www.becker.com/' },
  { keys: ['wiley'], url: () => 'https://www.efficientlearning.com/' },
  { keys: ['semrush'], url: () => 'https://www.semrush.com/academy/' },
  { keys: ['ahrefs'], url: () => 'https://ahrefs.com/academy' },
  { keys: ['ascm'], url: () => 'https://www.ascm.org/learning-development/' },
  { keys: ['sap learning'], url: () => 'https://learning.sap.com/' },
  { keys: ['oracle university'], url: () => 'https://education.oracle.com/' },
  { keys: ['kaplan'], url: () => 'https://www.kaptest.com/' },
  { keys: ['uworld'], url: () => 'https://www.uworld.com/' },
  { keys: ['osmosis'], url: () => 'https://www.osmosis.org/' },
  { keys: ['registered nurse rn'], url: () => 'https://www.registerednursern.com/' },
  { keys: ['rxprep'], url: () => 'https://www.uworld.com/pharmacy/rxprep' },
  { keys: ['medbridge'], url: () => 'https://www.medbridge.com/' },
  { keys: ['mathworks'], url: () => 'https://matlabacademy.mathworks.com/' },
  { keys: ['esri'], url: () => 'https://www.esri.com/training/' },
  { keys: ['canva'], url: () => 'https://www.canva.com/designschool/' },
  { keys: ['masterclass'], url: () => 'https://www.masterclass.com/' },
  { keys: ['youtube'], url: (q) => `https://www.youtube.com/results?search_query=${q}` },
  { keys: ['khan academy'], url: (q) => `https://www.khanacademy.org/search?page_search_query=${q}` },
  { keys: ['hugging face'], url: () => 'https://huggingface.co/learn' },
  { keys: ['fast.ai'], url: () => 'https://course.fast.ai/' },
  { keys: ['deeplearning.ai'], url: () => 'https://www.deeplearning.ai/courses/' },
  { keys: ['portswigger'], url: () => 'https://portswigger.net/web-security' },
  { keys: ['offsec'], url: () => 'https://www.offsec.com/courses-and-certifications/' },
  { keys: ['ec-council'], url: () => 'https://www.eccouncil.org/train-certify/' },
  { keys: ['istqb'], url: () => 'https://www.istqb.org/' },
  { keys: ['applitools'], url: () => 'https://testautomationu.applitools.com/' },
  { keys: ['apple developer'], url: () => 'https://developer.apple.com/learn/' },
  { keys: ['futurelearn'], url: (q) => `https://www.futurelearn.com/search?q=${q}` },
  { keys: ['ibm quantum'], url: () => 'https://learning.quantum.ibm.com/' },
  { keys: ['brilliant'], url: () => 'https://brilliant.org/courses/' },
  { keys: ['iapp'], url: () => 'https://iapp.org/train/' },
  { keys: ['y combinator'], url: () => 'https://www.startupschool.org/' },
  { keys: ['shopify'], url: () => 'https://www.shopify.com/learn' },
  { keys: ['meta blueprint'], url: () => 'https://www.facebook.com/business/learn' },
  { keys: ['salesforce'], url: () => 'https://trailhead.salesforce.com/' },
  { keys: ['hootsuite'], url: () => 'https://education.hootsuite.com/' },
  { keys: ['cfa institute'], url: () => 'https://www.cfainstitute.org/en/programs/cfa' },
  { keys: ['harvard business school'], url: () => 'https://online.hbs.edu/courses/' },
  { keys: ['aws'], url: (q) => `https://skillbuilder.aws/search?searchText=${q}` },
  { keys: ['acca'], url: () => 'https://www.accaglobal.com/gb/en/student.html' },
  { keys: ['radiopaedia'], url: () => 'https://radiopaedia.org/courses' },
  { keys: ['who'], url: () => 'https://openwho.org/courses' },
  { keys: ['nida clinical'], url: () => 'https://gcp.nidatraining.org/' },
  { keys: ['acrp'], url: () => 'https://acrpnet.org/courses' },
  { keys: ['tcm security'], url: () => 'https://academy.tcm-sec.com/' },
  { keys: ['blackmagic'], url: () => 'https://www.blackmagicdesign.com/products/davinciresolve/training' },
  { keys: ['iata'], url: () => 'https://www.iata.org/en/training/' },
  { keys: ['red cross'], url: () => 'https://www.redcross.org/take-a-class' },
  { keys: ['american heart'], url: () => 'https://cpr.heart.org/' },
  { keys: ['padi'], url: () => 'https://www.padi.com/courses' },
  { keys: ['upwork'], url: () => 'https://community.upwork.com/t5/Academy/ct-p/Academy' },
  { keys: ['javascript.info'], url: () => 'https://javascript.info/' },
];

export function getCourseUrl(course) {
  const direct = normalizeExternalUrl(course?.url);
  if (direct) return direct;

  const title = String(course?.title || '').trim();
  const platform = String(course?.platform || '').trim();
  const lowerTitle = title.toLowerCase();
  const lowerPlatform = platform.toLowerCase();

  const flagship = FLAGSHIP_COURSE_URLS.find((item) => item.match(lowerTitle));
  if (flagship) return flagship.url;

  const encodedTitle = encodeURIComponent(title || 'course');
  const resolver = PLATFORM_RESOLVERS.find((entry) =>
    entry.keys.some((k) => lowerPlatform.includes(k))
  );
  if (resolver) return resolver.url(encodedTitle);

  return `https://www.google.com/search?q=${encodeURIComponent(`${title} ${platform}`.trim())}`;
}

const UNIVERSITIES = {
  NUST: {
    name: 'NUST (National University of Sciences & Technology)',
    city: 'Islamabad',
    type: 'Public',
    admissionUrl: 'https://ugadmissions.nust.edu.pk/',
  },
  FAST: {
    name: 'FAST-NUCES',
    city: 'Islamabad / Lahore / Karachi',
    type: 'Private',
    admissionUrl: 'https://nu.edu.pk/Admissions/HowToApply',
  },
  LUMS: {
    name: 'LUMS (Lahore University of Management Sciences)',
    city: 'Lahore',
    type: 'Private',
    admissionUrl: 'https://admission.lums.edu.pk/',
  },
  COMSATS: {
    name: 'COMSATS University Islamabad',
    city: 'Islamabad / Lahore',
    type: 'Public',
    admissionUrl: 'https://admissions.comsats.edu.pk/',
  },
  ITU: {
    name: 'Information Technology University (ITU)',
    city: 'Lahore',
    type: 'Public',
    admissionUrl: 'https://itu.edu.pk/',
  },
  UET: {
    name: 'UET Lahore (University of Engineering & Technology)',
    city: 'Lahore',
    type: 'Public',
    admissionUrl: 'https://admission.uet.edu.pk/',
  },
  GIKI: {
    name: 'GIK Institute of Engineering Sciences & Technology',
    city: 'Topi, Swabi',
    type: 'Private',
    admissionUrl: 'https://admissions.giki.edu.pk/',
  },
  NED: {
    name: 'NED University of Engineering & Technology',
    city: 'Karachi',
    type: 'Public',
    admissionUrl: 'https://ugadmissions.neduet.edu.pk/',
  },
  AIR: {
    name: 'Air University',
    city: 'Islamabad',
    type: 'Public',
    admissionUrl: 'https://portals.au.edu.pk/admissions',
  },
  AKU: {
    name: 'Aga Khan University (AKU)',
    city: 'Karachi',
    type: 'Private',
    admissionUrl: 'https://www.aku.edu/admissions',
  },
  KEMU: {
    name: 'King Edward Medical University (KEMU)',
    city: 'Lahore',
    type: 'Public',
    admissionUrl: 'https://kemu.edu.pk/',
  },
  DUHS: {
    name: 'Dow University of Health Sciences (DUHS)',
    city: 'Karachi',
    type: 'Public',
    admissionUrl: 'https://admission-duhs.effii.app/application/index.php',
  },
  UHS: {
    name: 'University of Health Sciences (UHS)',
    city: 'Lahore',
    type: 'Public',
    admissionUrl: 'http://www.uhs.edu.pk/',
  },
  IBA: {
    name: 'IBA Karachi (Institute of Business Administration)',
    city: 'Karachi',
    type: 'Public',
    admissionUrl: 'https://onlineadmission.iba.edu.pk/',
  },
  QAU: {
    name: 'Quaid-i-Azam University (QAU)',
    city: 'Islamabad',
    type: 'Public',
    admissionUrl: 'http://ugadmissions.qau.edu.pk/',
  },
  PU: {
    name: 'University of the Punjab',
    city: 'Lahore',
    type: 'Public',
    admissionUrl: 'https://admissions.pu.edu.pk/',
  },
  BAHRIA: {
    name: 'Bahria University',
    city: 'Islamabad / Karachi / Lahore',
    type: 'Public',
    admissionUrl: 'https://bahria.edu.pk/',
  },
  SZABIST: {
    name: 'SZABIST University',
    city: 'Karachi / Islamabad',
    type: 'Private',
    admissionUrl: 'https://admissions.szabist.edu.pk/',
  },
  NCA: {
    name: 'National College of Arts (NCA)',
    city: 'Lahore / Rawalpindi',
    type: 'Public',
    admissionUrl: 'https://www.nca.edu.pk/',
  },
  UAF: {
    name: 'University of Agriculture, Faisalabad (UAF)',
    city: 'Faisalabad',
    type: 'Public',
    admissionUrl: 'https://admissions.uaf.edu.pk/',
  },
  UVAS: {
    name: 'University of Veterinary & Animal Sciences (UVAS)',
    city: 'Lahore',
    type: 'Public',
    admissionUrl: 'https://uvas.edu.pk/',
  },
  COTHM: {
    name: 'COTHM (College of Tourism & Hotel Management)',
    city: 'Lahore / Karachi / Islamabad',
    type: 'Private',
    admissionUrl: 'https://cothm.edu.pk/',
  },
  NAVTTC: {
    name: 'NAVTTC Vocational & Technical Institutes',
    city: 'Nationwide',
    type: 'Government',
    admissionUrl: 'https://nsis.navttc.gov.pk/',
  },
};

const withProgram = (uni, program) => ({ ...uni, program });

const CAREER_UNIVERSITY_GROUPS = [
  {
    keywords: ['plumber', 'hvac', 'welder', 'automotive technician'],
    list: () => [
      withProgram(UNIVERSITIES.NAVTTC, 'National Vocational & Technical Trade Certificate'),
      withProgram(UNIVERSITIES.NED, 'BS / BE Automotive & Mechanical Technology'),
      withProgram(UNIVERSITIES.UET, 'B.Sc. Mechanical & Energy Technology'),
    ],
  },
  {
    keywords: ['pilot', 'aviation', 'aircraft', 'flight attendant', 'cabin crew', 'drone', 'uav', 'astronaut'],
    list: () => [
      withProgram(UNIVERSITIES.AIR, 'BS Aviation Management / BE Aerospace & Avionics'),
      withProgram(UNIVERSITIES.NUST, 'BE Aerospace / Avionics Engineering'),
      withProgram(UNIVERSITIES.NED, 'BE Mechanical & Aerospace-Allied Programs'),
      withProgram(UNIVERSITIES.COTHM, 'Aviation, Cabin Crew & Hospitality Operations'),
    ],
  },
  {
    keywords: ['tourism', 'travel', 'hotel'],
    list: () => [
      withProgram(UNIVERSITIES.COTHM, 'BS Hospitality & Tourism Management'),
      withProgram(UNIVERSITIES.PU, 'BS Tourism & Hospitality Management'),
      withProgram(UNIVERSITIES.BAHRIA, 'BS Hospitality & Tourism Studies'),
      withProgram(UNIVERSITIES.SZABIST, 'BBA / Tourism & Service Management'),
    ],
  },
  {
    keywords: [
      'physician', 'nurse', 'pharmacist', 'therapist', 'psychiatrist', 'cardiologist',
      'dermatologist', 'pediatrician', 'gynecologist', 'obstetrician', 'anesthesiologist',
      'radiologist', 'surgeon', 'public health', 'epidemiologist', 'clinical research',
      'medical laboratory', 'health informatics', 'pathologist',
    ],
    list: (title) => {
      const lower = title.toLowerCase();
      const prog = lower.includes('nurse')
        ? 'BS Nursing (BScN)'
        : lower.includes('pharmacist')
          ? 'Doctor of Pharmacy (Pharm-D)'
          : lower.includes('physical therapist') || lower.includes('occupational therapist')
            ? 'Doctor of Physical Therapy (DPT) / Rehabilitation Sciences'
            : lower.includes('medical laboratory')
              ? 'BS Medical Laboratory Technology (MLT)'
              : lower.includes('public health') || lower.includes('epidemiologist') || lower.includes('health informatics')
                ? 'BS / MSPH Public Health & Health Informatics'
                : 'MBBS & Postgraduate Medical Specialization';

      return [
        withProgram(UNIVERSITIES.KEMU, prog),
        withProgram(UNIVERSITIES.AKU, prog),
        withProgram(UNIVERSITIES.DUHS, prog),
        withProgram(UNIVERSITIES.UHS, prog),
        withProgram(UNIVERSITIES.QAU, 'BS / MPhil Pharmacy, Microbiology & Public Health'),
      ];
    },
  },
  {
    keywords: ['psychologist', 'counselor', 'teacher', 'professor', 'instructional designer', 'education'],
    list: (title) => {
      const lower = title.toLowerCase();
      const prog = lower.includes('psycholog') || lower.includes('counselor')
        ? 'BS / MS Clinical & Applied Psychology'
        : 'BS / B.Ed / MS Education & Instructional Design';
      return [
        withProgram(UNIVERSITIES.QAU, prog),
        withProgram(UNIVERSITIES.PU, prog),
        withProgram(UNIVERSITIES.BAHRIA, prog),
        withProgram(UNIVERSITIES.LUMS, 'BS Social Sciences & Education Leadership'),
      ];
    },
  },
  {
    keywords: ['biotechnologist', 'food scientist', 'food technologist', 'marine biologist', 'environmental scientist', 'forensic', 'astrophysicist'],
    list: (title) => {
      const lower = title.toLowerCase();
      const prog = lower.includes('food')
        ? 'BS Food Science & Technology'
        : lower.includes('biotech')
          ? 'BS Biotechnology & Genetic Engineering'
          : lower.includes('environmental')
            ? 'BS / BE Environmental Sciences & Engineering'
            : lower.includes('astrophysicist')
              ? 'BS Physics / Space & Astrophysics'
              : 'BS Biological, Forensic & Life Sciences';
      return [
        withProgram(UNIVERSITIES.QAU, prog),
        withProgram(UNIVERSITIES.UAF, prog),
        withProgram(UNIVERSITIES.UVAS, prog),
        withProgram(UNIVERSITIES.PU, prog),
        withProgram(UNIVERSITIES.NUST, prog),
      ];
    },
  },
  {
    keywords: ['lawyer', 'civil services', 'css', 'compliance officer', 'intelligence analyst'],
    list: (title) => {
      const lower = title.toLowerCase();
      const prog = lower.includes('lawyer')
        ? 'BA-LL.B (Hons) / Corporate & Criminal Law'
        : 'BS International Relations, Governance & Public Policy';
      return [
        withProgram(UNIVERSITIES.LUMS, prog),
        withProgram(UNIVERSITIES.PU, prog),
        withProgram(UNIVERSITIES.QAU, prog),
        withProgram(UNIVERSITIES.BAHRIA, prog),
      ];
    },
  },
  {
    keywords: ['architect', 'graphic designer', 'ux designer', 'film', 'video editor', 'photographer', 'music', 'actor', 'author', 'writer', 'content creator', 'news anchor', 'makeup', 'game designer'],
    list: (title) => {
      const lower = title.toLowerCase();
      const prog = lower.includes('architect')
        ? 'B.Arch (Bachelor of Architecture)'
        : lower.includes('ux')
          ? 'BS Interaction & Product Design / CS'
          : 'BFA / BS Media Sciences, Film & Visual Design';
      return [
        withProgram(UNIVERSITIES.NCA, prog),
        withProgram(UNIVERSITIES.NUST, lower.includes('architect') ? 'B.Arch / BS Industrial Design' : 'BS Mass Communication & Design'),
        withProgram(UNIVERSITIES.SZABIST, 'BS Media Sciences, Digital Design & Film'),
        withProgram(UNIVERSITIES.UET, 'B.Arch / Product & Industrial Design'),
      ];
    },
  },
  {
    keywords: [
      'software', 'developer', 'data', 'machine learning', 'ai ', 'cyber', 'cloud',
      'devops', 'sre', 'site reliability', 'qa ', 'ethical hacker', 'ar/vr', 'quantum',
      'it support', 'data center', 'esports',
    ],
    list: (title) => [
      withProgram(UNIVERSITIES.NUST, `BS Computer Science / Software Engineering (${title})`),
      withProgram(UNIVERSITIES.FAST, 'BS Computer Science, AI, Data Science & Cyber Security'),
      withProgram(UNIVERSITIES.LUMS, 'BS Computer Science (SBASSE)'),
      withProgram(UNIVERSITIES.COMSATS, 'BS Computer Science / Software Engineering'),
      withProgram(UNIVERSITIES.ITU, 'BS Computer Science & Artificial Intelligence'),
    ],
  },
  {
    keywords: [
      'engineer', 'mechanical', 'electrical', 'chemical', 'industrial', 'construction',
      'renewable energy', 'semiconductor', 'embedded', 'iot', 'robotics',
    ],
    list: (title) => [
      withProgram(UNIVERSITIES.NUST, `BE / BS in ${title.replace(/engineer/i, '').trim() || 'Engineering'} Sciences`),
      withProgram(UNIVERSITIES.UET, `B.Sc. ${title}`),
      withProgram(UNIVERSITIES.GIKI, 'BS Engineering Sciences & Applied Technology'),
      withProgram(UNIVERSITIES.NED, `BE ${title.replace(/engineer/i, '').trim() || 'Engineering'}`),
      withProgram(UNIVERSITIES.AIR, 'BE Electrical, Mechanical & Mechatronics Engineering'),
    ],
  },
  {
    keywords: [
      'business', 'finance', 'financial', 'accountant', 'banking', 'marketing', 'seo',
      'product manager', 'project manager', 'human resources', 'supply chain', 'consultant',
      'investment', 'entrepreneur', 'e-commerce', 'customer success', 'social media',
      'operations', 'sustainability', 'esg', 'quant', 'sap', 'erp', 'virtual assistant',
    ],
    list: (title) => [
      withProgram(UNIVERSITIES.IBA, `BBA / BS Accounting, Finance & Business (${title})`),
      withProgram(UNIVERSITIES.LUMS, 'BSc Management Science / Accounting & Finance (SDSB)'),
      withProgram(UNIVERSITIES.NUST, 'BBA / BS Accounting, Finance & Economics (NBS)'),
      withProgram(UNIVERSITIES.SZABIST, 'BBA / BS Business & Digital Management'),
      withProgram(UNIVERSITIES.COMSATS, 'BBA / BS Business Analytics'),
    ],
  },
];

export function getUniversitiesForCareer(career) {
  if (Array.isArray(career?.universities) && career.universities.length > 0) {
    return career.universities
      .map((uni) => ({
        name: uni.name || '',
        program: uni.program || '',
        city: uni.city || '',
        type: uni.type || '',
        admissionUrl: normalizeExternalUrl(uni.admissionUrl || uni.url),
      }))
      .filter((uni) => uni.name && uni.admissionUrl);
  }

  const title = String(career?.title || '').trim();
  const lower = title.toLowerCase();

  const group = CAREER_UNIVERSITY_GROUPS.find((g) =>
    g.keywords.some((kw) => lower.includes(kw))
  );

  if (group) return group.list(title);

  return [
    withProgram(UNIVERSITIES.NUST, `BS / MS Programs aligned with ${title || 'this field'}`),
    withProgram(UNIVERSITIES.LUMS, `Undergraduate & Graduate Programs`),
    withProgram(UNIVERSITIES.QAU, `BS / MPhil Academic Programs`),
    withProgram(UNIVERSITIES.PU, `Undergraduate & Postgraduate Admissions`),
  ];
}

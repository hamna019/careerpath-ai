import { Award, BookOpen, ExternalLink, GraduationCap, MapPin } from 'lucide-react';
import { getCourseUrl, getUniversitiesForCareer } from '../utils/careerResources';

export function UniversitiesSection({ career }) {
  const universities = getUniversitiesForCareer(career);

  return (
    <div className="bg-white p-7 rounded-[25px] prototype-card-border custom-card-shadow space-y-5">
      <div className="space-y-1">
        <h3 className="text-[22px] font-semibold text-gray-900 tracking-tight flex items-center gap-2">
          <GraduationCap size={22} className="text-[#890080]" /> Universities Offering This Program
        </h3>
        <p className="text-[14px] text-[#545454] font-normal">
          Explore universities offering relevant degree programs and open their official admission portals.
        </p>
      </div>

      <div className="space-y-3">
        {universities.length > 0 ? universities.map((uni, idx) => (
          <div
            key={`${uni.name}-${idx}`}
            className="bg-[#FCF8FE] p-4.5 rounded-2xl border border-[#FFD2F7] flex flex-col sm:flex-row sm:items-center justify-between gap-4"
          >
            <div className="space-y-1.5 min-w-0">
              <div className="flex items-center gap-2.5 flex-wrap">
                <h4 className="text-[17px] font-bold text-gray-900 break-words">{uni.name}</h4>
                {uni.type && (
                  <span className="px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-[#890080] bg-[#FFE1FD] border border-[#F45EE4]/40">
                    {uni.type}
                  </span>
                )}
              </div>
              {uni.program && (
                <p className="text-[14px] text-[#545454] font-medium break-words">{uni.program}</p>
              )}
              {uni.city && (
                <div className="flex items-center gap-1.5 text-[13px] text-gray-500">
                  <MapPin size={14} className="text-[#890080] flex-shrink-0" />
                  <span>{uni.city}</span>
                </div>
              )}
            </div>

            <a
              href={uni.admissionUrl}
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-full text-[13px] font-semibold bg-[#FFD7FC] text-[#890080] border border-[#FF00D3]/40 hover:bg-[#890080] hover:text-white transition-all flex-shrink-0 whitespace-nowrap"
            >
              <span>Admission Portal</span>
              <ExternalLink size={14} />
            </a>
          </div>
        )) : (
          <div className="text-gray-500 text-center py-4 text-[15px]">
            No universities listed yet for this career.
          </div>
        )}
      </div>
    </div>
  );
}

export function RecommendedCoursesSection({ courses = [], loading = false }) {
  return (
    <div className="bg-white p-7 rounded-[25px] prototype-card-border custom-card-shadow space-y-5">
      <div className="space-y-1">
        <h3 className="text-[22px] font-semibold text-gray-900 tracking-tight flex items-center gap-2">
          <BookOpen size={22} className="text-[#890080]" /> Recommended Courses
        </h3>
        <p className="text-[14px] text-[#545454] font-normal">
          Click any course below to go directly to the offering platform.
        </p>
      </div>

      <div className="space-y-3">
        {courses.length > 0 ? courses.map((course, cIdx) => {
          const courseUrl = getCourseUrl(course);
          return (
            <a
              key={cIdx}
              href={courseUrl}
              target="_blank"
              rel="noopener noreferrer"
              className="bg-[#FCF8FE] p-4.5 rounded-2xl border border-[#FFD2F7] hover:border-[#890080]/50 hover:shadow-sm transition-all flex items-center justify-between gap-3 group block"
            >
              <div className="space-y-1 min-w-0">
                <h4 className="text-[17px] font-bold text-gray-900 group-hover:text-[#890080] transition-colors break-words">
                  {course.title}
                </h4>
                <div className="flex flex-wrap items-center gap-x-4 gap-y-1 text-[14px] text-[#545454]">
                  <span>{course.platform}</span>
                  {course.rating && (
                    <span className="flex items-center gap-1.5 text-[#F59E0B] font-medium">
                      <Award size={15} /> {course.rating}
                    </span>
                  )}
                  <span className="inline-flex items-center gap-1 text-[#890080] font-semibold text-[13px]">
                    Open Course <ExternalLink size={13} />
                  </span>
                </div>
              </div>
              <div className="text-[#2563EB] font-bold text-[16px] flex-shrink-0">
                {course.cost}
              </div>
            </a>
          );
        }) : (
          <div className="text-gray-500 text-center py-4 text-[15px]">
            {loading ? 'Loading courses...' : 'No recommended courses available yet for this career.'}
          </div>
        )}
      </div>
    </div>
  );
}

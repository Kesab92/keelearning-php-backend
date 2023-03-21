import ContentEditor from "./components/courses/views/editors/ContentEditor"
import ChapterEditor from "./components/courses/views/editors/ChapterEditor"
import CourseStatisticsProgress from "./components/course-statistics/CourseStatisticsProgress"
import CourseStatisticsTests from "./components/course-statistics/CourseStatisticsTests"
import CourseStatisticsTestDetails from "./components/course-statistics/CourseStatisticsTestDetails"
import CourseStatisticsForms from "./components/course-statistics/CourseStatisticsForms"
import CourseStatisticsFormDetails from "./components/course-statistics/CourseStatisticsFormDetails"
import CourseStatisticsWBTs from "./components/course-statistics/CourseStatisticsWBTs"
import CourseStatisticsWBTDetails from "./components/course-statistics/CourseStatisticsWBTDetails"
import CourseContents from "./components/courses/views/CourseContents"
import CourseEditTodolists from "./components/courses/views/CourseTodolists.vue"
import CourseEditTodolistResponse from "./components/courses/views/CourseTodolistResponse.vue"
import CourseManagers from "./components/courses/views/CourseManagers"
import LearningmaterialsIndex from "./components/learningmaterials/Index"
import LearningmaterialEditGeneral from "./components/learningmaterials/EditGeneral.vue"
import LearningmaterialEditDesign from "./components/learningmaterials/EditDesign.vue"
import LearningmaterialEditDescription from "./components/learningmaterials/EditDescription.vue"
import LearningMaterialComments from "./components/learningmaterials/Comments"
import LearningmaterialFolderEditGeneral from "./components/learningmaterials/FolderEditGeneral"
import LearningmaterialUsages from "./components/learningmaterials/Usages"
import AdvertisementsIndex from "./components/advertisements/Index"
import AdvertisementEditGeneral from "./components/advertisements/EditGeneral"
import UsersIndex from "./components/users/Index"
import UserEditGeneral from "./components/users/EditGeneral"
import UserEditPermissions from "./components/users/EditPermissions"
import UserSendMessage from "./components/users/SendMessage"
import UserRolesIndex from "./components/users/roles/Index"
import UserRolesEditGeneral from "./components/users/roles/sidebar/EditGeneral"
import UserRolesEditRights from "./components/users/roles/sidebar/EditRights"
import QualificationHistory from "./components/users/QualificationHistory"
import AppSwitcher from "./components/app-switcher/AppSwitcher"
import AppSwitcherDetails from "./components/app-switcher/AppSwitcherDetails"
import ManageUser from "./components/users/ManageUser"
import EditUserNotifications from "./components/users/EditNotifications"
import NewsIndex from "./components/news/Index"
import NewsEditGeneral from "./components/news/EditGeneral"
import NewsEditDesign from "./components/news/EditDesign"
import NewsComments from "./components/news/Comments"
import TestsIndex from "./components/tests/Index"
import Courses from "./components/courses/views/Courses"
import CourseCategories from "./components/courses/views/CourseCategories"
import CourseTemplates from "./components/courses/views/CourseTemplates"
import CourseEditChapter from "./components/courses/views/editors/ChapterEditor"
import CourseEditGeneral from "./components/courses/EditGeneral"
import CourseEditContent from "./components/courses/views/editors/ContentEditor"
import CourseEditContents from "./components/courses/EditContents"
import CourseEditSettings from "./components/courses/EditSettings"
import CourseEditTemplateInheritance from "./components/courses/EditTemplateInheritance"
import CourseComments from "./components/courses/Comments.vue"
import ContentCategoryEditGeneral from "./components/partials/content-categories/ContentCategoryEditGeneral"
import KeywordsIndex from "./components/keywords/Index"
import KeywordEditGeneral from "./components/keywords/EditGeneral"
import KeywordCategories from "./components/keywords/KeywordCategories"
import StatsUsers from "./components/stats/Users"
import UserReportings from "./components/stats/UserReportings"
import PageIndex from "./components/pages/Index"
import PageEditGeneral from "./components/pages/EditGeneral"
import PageEditContent from "./components/pages/EditContent"
import PageEditSubpage from "./components/pages/EditSubpage"
import Settings from "./components/settings/Settings"
import CommentsIndex from "./components/comments/Index"
import TagIndex from "./components/tags/Index"
import TagCategories from "./components/tags/TagCategories"
import TagEditGeneral from "./components/tags/EditGeneral"
import VouchersIndex from "./components/vouchers/Index"
import VoucherEditGeneral from "./components/vouchers/EditGeneral"
import SuggestedQuestionsIndex from "./components/suggested-questions/Index"
import SuggestedQuestionEditGeneral from "./components/suggested-questions/EditGeneral"
import QuizReportings from "./components/reportings/Quiz"
import ReportingEditGeneral from "./components/partials/reportings/ReportingEditGeneral"
import PlayerQuizTab from "./components/stats/quiz/PlayerTab"
import QuestionQuizTab from "./components/stats/quiz/QuestionTab"
import CategoryQuizTab from "./components/stats/quiz/CategoryTab"
import QuizTeamQuizTab from "./components/stats/quiz/QuizTeamTab"
import QuestionIndex from './components/questions/Index'
import QuestionEditGeneral from './components/questions/EditGeneral'
import QuestionEditSettings from './components/questions/EditSettings'
import QuizTeamIndex from "./components/quiz-teams/Index"
import QuizTeamEditGeneral from "./components/quiz-teams/EditGeneral"
import ReportEditGeneral from "./components/reports/EditGeneral"
import AppointmentIndex from "./components/appointments/Index"
import AppointmentEditGeneral from "./components/appointments/EditGeneral"
import AppointmentEditParticipants from "./components/appointments/EditParticipants"
import FormCategories from "./components/forms/views/FormCategories"
import FormIndex from "./components/forms/views/Forms"
import FormEditGeneral from "./components/forms/views/EditGeneral"
import FormUsages from "./components/forms/views/Usages"
import FormStats from "./components/forms/views/Stats"
import UserActivity from "./components/superadmin/UserActivity"

function mapChildRoutesCallback(prefix) {
  return (route) => {
    route = { ...route }
    route.path = `/${prefix}${route.path}`
    route.name = `${prefix}.${route.name}`
    if (route.children) {
      route.children = route.children.map(mapChildRoutesCallback(prefix))
    }
    return route
  }
}

const coursesChildRoutes = [
  {
    path: '/courses/:courseId/general',
    component: CourseEditGeneral,
    name: 'courses.edit.general',
  },
  {
    path: '/courses/:courseId/settings',
    component: CourseEditSettings,
    name: 'courses.edit.settings',
  },
  {
    path: '/courses/:courseId/template-inheritance',
    component: CourseEditTemplateInheritance,
    name: 'courses.edit.templateInheritance',
  },
  {
    path: '/courses/:courseId/comments/:commentId?',
    component: CourseComments,
    name: 'courses.edit.comments',
  },
  {
    path: '/courses/:courseId/contents',
    component: CourseEditContents,
    name: 'courses.edit.contents',
    meta: {
      wideSidebar: true,
    },
    children: [
      {
        path: '/courses/:courseId/contents/content/:contentId',
        component: CourseEditContent,
        name: 'courses.edit.contents.content',
        meta: {
          wideSidebar: true,
        },
      },
      {
        path: '/courses/:courseId/contents/chapter/:chapterId',
        component: CourseEditChapter,
        name: 'courses.edit.contents.chapter',
        meta: {
          wideSidebar: true,
        },
      },
    ],
  },
  {
    path: '/courses/:courseId/todolists/:contentId?',
    component: CourseEditTodolists,
    name: 'courses.edit.todolists',
    children: [
      {
        path: '/courses/:courseId/todolists/:contentId/:participationId',
        name: 'courses.edit.todolists.results',
        component: CourseEditTodolistResponse,
      }
    ]
  },
]

const newsChildRoutes = [
  {
    path: '/news/:newsId/general',
    component: NewsEditGeneral,
    name: 'news.edit.general',
  },
  {
    path: '/news/:newsId/design',
    component: NewsEditDesign,
    name: 'news.edit.design',
  },
  {
    path: '/news/:newsId/comments/:commentId?',
    component: NewsComments,
    name: 'news.edit.comments',
  },
]

const learningMaterialChildRoutes = [
  {
    path: '/learningmaterials/:folderId/general',
    component: LearningmaterialFolderEditGeneral,
    name: 'learningmaterials.folder.edit.general',
  },
  {
    path: '/learningmaterials/:folderId/:learningmaterialId/general',
    component: LearningmaterialEditGeneral,
    name: 'learningmaterials.edit.general',
  },
  {
    path: '/learningmaterials/:folderId/:learningmaterialId/design',
    component: LearningmaterialEditDesign,
    name: 'learningmaterials.edit.design',
  },
  {
    path: '/learningmaterials/:folderId/:learningmaterialId/description',
    component: LearningmaterialEditDescription,
    name: 'learningmaterials.edit.description',
  },
  {
    path: '/learningmaterials/:folderId/:learningmaterialId/comments/:commentId?',
    component: LearningMaterialComments,
    name: 'learningmaterials.edit.comments',
  },
  {
    path: '/learningmaterials/:folderId/:learningmaterialId/usages',
    component: LearningmaterialUsages,
    name: 'learningmaterials.edit.usages',
  },
]

export default [
  {
    path: '/appswitcher',
    component: AppSwitcher,
    name: 'appswitcher',
  },
  {
    path: '/appswitcher/details',
    component: AppSwitcherDetails,
    name: 'appswitcher.details',
  },
  {
    path: '/settings/:profileId/:area',
    component: Settings,
    name: 'settings',
  },
  {
    path: '/courses',
    component: Courses,
    name: 'courses.index',
    children: coursesChildRoutes,
  },
  {
    path: '/courses/templates',
    component: CourseTemplates,
    name: 'courses.templates',
    children: [
      {
        path: '/courses/templates/:courseId/general',
        component: CourseEditGeneral,
        name: 'courses.templates.edit.general',
      },
      {
        path: '/courses/templates/:courseId/settings',
        component: CourseEditSettings,
        name: 'courses.templates.edit.settings',
      },
      {
        path: '/courses/templates/:courseId/template-inheritance',
        component: CourseEditTemplateInheritance,
        name: 'courses.templates.edit.templateInheritance',
      },
      {
        path: '/courses/templates/:courseId/contents',
        component: CourseEditContents,
        name: 'courses.templates.edit.contents',
        meta: {
          wideSidebar: true,
        },
        children: [
          {
            path: '/courses/templates/:courseId/contents/content/:contentId',
            component: CourseEditContent,
            name: 'courses.templates.edit.contents.content',
            meta: {
              wideSidebar: true,
            },
          },
          {
            path: '/courses/templates/:courseId/contents/chapter/:chapterId',
            component: CourseEditChapter,
            name: 'courses.templates.edit.contents.chapter',
            meta: {
              wideSidebar: true,
            },
          },
        ],
      },
    ],
  },
  {
    path: '/courses/categories',
    component: CourseCategories,
    name: 'courses.categories',
    children: [
      {
        path: '/courses/categories/:categoryId/general',
        component: ContentCategoryEditGeneral,
        name: 'courses.categories.edit.general',
      },
    ],
  },
  {
    path: '/course/managers',
    component: CourseManagers,
    name: 'course.managers',
  },
  {
    path: '/course/comments/:commentId?',
    component: CourseComments,
    name: 'course.comments',
  },
  {
    path: '/course/contents',
    component: CourseContents,
    name: 'course.contents',
    children: [
      {
        path: 'content/:id?',
        component: ContentEditor,
        name: 'course.content',
      },
      {
        path: 'chapter/:id',
        component: ChapterEditor,
        name: 'course.chapter',
      },
    ],
  },
  {
    path: '/course-statistics',
    component: CourseStatisticsProgress,
    name: 'course-statistics.progress',
  },
  {
    path: '/course-statistics/tests',
    component: CourseStatisticsTests,
    name: 'course-statistics.tests',
    children: [
      {
        path: ':testId',
        component: CourseStatisticsTestDetails,
        name: 'course-statistics.test-details'
      }
    ]
  },
  {
    path: '/course-statistics/forms',
    component: CourseStatisticsForms,
    name: 'course-statistics.forms',
    children: [
      {
        path: ':courseContentId',
        component: CourseStatisticsFormDetails,
        name: 'course-statistics.form-details'
      }
    ]
  },
  {
    path: '/course-statistics/wbts',
    component: CourseStatisticsWBTs,
    name: 'course-statistics.wbts',
    children: [
      {
        path: ':wbtId',
        component: CourseStatisticsWBTDetails,
        name: 'course-statistics.wbt-details'
      }
    ]
  },
  {
    path: '/learningmaterials/:folderId?',
    component: LearningmaterialsIndex,
    name: 'learningmaterials.index',
    children: learningMaterialChildRoutes,
  },
  {
    path: '/advertisements',
    component: AdvertisementsIndex,
    name: 'advertisements.index',
    children: [
      {
        path: '/advertisements/:advertisementId/general',
        component: AdvertisementEditGeneral,
        name: 'advertisements.edit.general',
      },
    ],
  },
  {
    path: '/users/:config?',
    component: UsersIndex,
    name: 'users.index',
    children: [
      {
        path: '/users/:userId/general',
        component: UserEditGeneral,
        name: 'users.edit.general',
      },
      {
        path: '/users/:userId/permissions',
        component: UserEditPermissions,
        name: 'users.edit.permissions',
      },
      {
        path: '/users/:userId/management',
        component: ManageUser,
        name: 'users.edit.management',
      },
      {
        path: '/users/:userId/notifications',
        component: EditUserNotifications,
        name: 'users.edit.notifications',
      },
      {
        path: '/users/:userId/message',
        component: UserSendMessage,
        name: 'users.send.message',
      },
      {
        path: '/users/:userId/vouchers/:voucherId/general',
        component: VoucherEditGeneral,
        name: 'users.vouchers.edit.general',
      },
      {
        path: '/users/:userId/qualification-history',
        component: QualificationHistory,
        name: 'users.show.qualification-history',
      },
    ],
  },
  {
    path: '/user-roles',
    component: UserRolesIndex,
    name: 'user-roles.index',
    children: [
      {
        path: '/user-roles/:userRoleId/general',
        component: UserRolesEditGeneral,
        name: 'user-roles.edit.general',
      },
      {
        path: '/user-roles/:userRoleId/rights',
        component: UserRolesEditRights,
        name: 'user-roles.edit.rights',
      },
    ],
  },
  {
    path: '/news',
    component: NewsIndex,
    name: 'news.index',
    children: newsChildRoutes,
  },
  {
    path: '/tests',
    component: TestsIndex,
    name: 'tests.index',
  },
  {
    path: '/keywords',
    component: KeywordsIndex,
    name: 'keywords.index',
    children: [
      {
        path: '/keywords/:keywordId/general',
        component: KeywordEditGeneral,
        name: 'keywords.edit.general',
      },
    ],
  },
  {
    path: '/keywords/categories',
    component: KeywordCategories,
    name: 'keywords.categories',
    children: [
      {
        path: '/keywords/categories/:categoryId/general',
        component: ContentCategoryEditGeneral,
        name: 'keywords.categories.edit.general',
      },
    ],
  },
  {
    path: '/stats/users',
    component: StatsUsers,
    name: 'stats.users',
    children: [
      {
        path: 'reports/:reportType/general',
        component: ReportEditGeneral,
        name: 'stats.users.reports.edit.general',
      },
    ],
  },
  {
    path: '/pages',
    component: PageIndex,
    name: 'pages.index',
    children: [
      {
        path: '/pages/:pageId/general',
        component: PageEditGeneral,
        name: 'pages.edit.general',
      },
      {
        path: '/pages/:pageId/content',
        component: PageEditContent,
        name: 'pages.edit.content',
      },
      {
        path: '/pages/:pageId/subpage',
        component: PageEditSubpage,
        name: 'pages.edit.subpage',
      },
    ],
  },
  {
    path: '/comments/:config?',
    component: CommentsIndex,
    name: 'comments.index',
    children: [
      ...coursesChildRoutes,
      ...learningMaterialChildRoutes,
      ...newsChildRoutes,
    ].map(mapChildRoutesCallback('comments')),
  },
  {
    path: '/tags',
    component: TagIndex,
    name: 'tags.index',
    children: [
      {
        path: '/tags/:tagId/general',
        component: TagEditGeneral,
        name: 'tags.edit.general',
      },
    ],
  },
  {
    path: '/tags/categories',
    component: TagCategories,
    name: 'tags.categories',
    children: [
      {
        path: '/tags/categories/:categoryId/general',
        component: ContentCategoryEditGeneral,
        name: 'tags.categories.edit.general',
      },
    ],
  },
  {
    path: '/vouchers',
    component: VouchersIndex,
    name: 'vouchers.index',
    children: [
      {
        path: '/vouchers/:voucherId/general',
        component: VoucherEditGeneral,
        name: 'vouchers.edit.general',
      },
    ],
  },
  {
    path: '/suggested-questions',
    component: SuggestedQuestionsIndex,
    name: 'suggested-questions.index',
    children: [
      {
        path: '/suggested-questions/:suggestedQuestionId/general',
        component: SuggestedQuestionEditGeneral,
        name: 'suggested-questions.edit.general',
      },
    ],
  },
  {
    path: '/stats/quiz/reporting',
    component: QuizReportings,
    name: 'stats.quiz.reportings',
    children: [
      {
        path: '/stats/quiz/reporting/:reportingId/general',
        component: ReportingEditGeneral,
        name: 'stats.quiz.reportings.edit.general',
      },
    ],
  },
  {
    path: '/stats/users/reporting',
    component: UserReportings,
    name: 'stats.users.reportings',
    children: [
      {
        path: '/stats/users/reporting/:reportingId/general',
        component: ReportingEditGeneral,
        name: 'stats.users.reportings.edit.general',
      },
    ],
  },
  {
    path: '/stats/quiz/questions',
    component: QuestionQuizTab,
    name: 'stats.quiz.questions',
  },
  {
    path: '/stats/quiz/players',
    component: PlayerQuizTab,
    name: 'stats.quiz.players',
  },
  {
    path: '/stats/quiz/categories',
    component: CategoryQuizTab,
    name: 'stats.quiz.categories',
  },
  {
    path: '/stats/quiz/quiz-teams',
    component: QuizTeamQuizTab,
    name: 'stats.quiz.quizTeams',
  },
  {
    path: '/questions/:config?',
    component: QuestionIndex,
    name: 'questions.index',
    children: [
      {
        path: '/questions/:questionId/general',
        component: QuestionEditGeneral,
        name: 'questions.edit.general',
      },
      {
        path: '/questions/:questionId/settings',
        component: QuestionEditSettings,
        name: 'questions.edit.settings',
      },
    ],
  },
  {
    path: '/quiz-teams',
    component: QuizTeamIndex,
    name: 'quizTeams.index',
    children: [
      {
        path: '/quiz-team/:quizTeamId/general',
        component: QuizTeamEditGeneral,
        name: 'quizTeams.edit.general',
      },
    ],
  },
  {
    path: '/appointments:config?',
    component: AppointmentIndex,
    name: 'appointments.index',
    children: [
      {
        path: '/appointments/:appointmentId/general',
        component: AppointmentEditGeneral,
        name: 'appointments.edit.general',
      },
      {
        path: '/appointments/:appointmentId/participants',
        component: AppointmentEditParticipants,
        name: 'appointments.edit.participants',
      },
    ],
  },
  {
    path: '/forms/categories',
    component: FormCategories,
    name: 'forms.categories',
    children: [
      {
        path: '/forms/categories/:categoryId/general',
        component: ContentCategoryEditGeneral,
        name: 'forms.categories.edit.general',
      },
    ],
  },
  {
    path: '/forms',
    component: FormIndex,
    name: 'forms.index',
    children: [
      {
        path: '/forms/:formId/general',
        component: FormEditGeneral,
        name: 'forms.edit.general',
      },
      {
        path: '/forms/:formId/usages',
        component: FormUsages,
        name: 'forms.edit.usages',
      },
      {
        path: '/forms/:formId/stats',
        component: FormStats,
        name: 'forms.edit.stats',
      },
    ],
  },
  {
    path: '/superadmin/user-activity',
    component: UserActivity,
    name: 'superadmin.user-activity',
  },
]

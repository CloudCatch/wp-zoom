"use strict";function _typeof(e){return(_typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}var _simplyCountdownMin=_interopRequireDefault(require("simplycountdown.js/dist/simplyCountdown.min.js")),dayjs=_interopRequireWildcard(require("dayjs")),_utc=_interopRequireDefault(require("dayjs/plugin/utc")),_timezone=_interopRequireDefault(require("dayjs/plugin/timezone"));function _getRequireWildcardCache(){if("function"!=typeof WeakMap)return null;var e=new WeakMap;return _getRequireWildcardCache=function(){return e},e}function _interopRequireWildcard(e){if(e&&e.__esModule)return e;if(null===e||"object"!==_typeof(e)&&"function"!=typeof e)return{default:e};var t=_getRequireWildcardCache();if(t&&t.has(e))return t.get(e);var r={},n=Object.defineProperty&&Object.getOwnPropertyDescriptor;for(var o in e)if(Object.prototype.hasOwnProperty.call(e,o)){var u=n?Object.getOwnPropertyDescriptor(e,o):null;u&&(u.get||u.set)?Object.defineProperty(r,o,u):r[o]=e[o]}return r.default=e,t&&t.set(e,r),r}function _interopRequireDefault(e){return e&&e.__esModule?e:{default:e}}dayjs.extend(_utc.default),dayjs.extend(_timezone.default),console.log(dayjs.tz.guess()),console.log(dayjs().get("year"));var multipleElements=document.querySelectorAll(".my-countdown");(0,_simplyCountdownMin.default)(multipleElements,{});

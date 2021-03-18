import simplyCountdown from 'simplycountdown.js/dist/simplyCountdown.min.js';
import * as dayjs from 'dayjs'
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';

dayjs.extend(utc)
dayjs.extend(timezone)

console.log(dayjs.tz.guess());
console.log(dayjs().get('year'));

let multipleElements = document.querySelectorAll('.my-countdown');
simplyCountdown(multipleElements, { /* options */ });
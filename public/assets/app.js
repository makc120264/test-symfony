var rows = document.querySelectorAll("[id ^= request]");
let tzOffset = new Date().getTimezoneOffset() * 60 * 1000;

for (let row of rows) {
    let rowId = row.getAttribute('id');
    let created = row.querySelector(".createdAt").innerHTML
    let createdRow = new Date(created);
    let createdTs = Date.parse(created);
    let nowTime = Date.now() + tzOffset;
    if ((nowTime - createdTs) > 3600000) {
        row.style.backgroundColor = "red";
    }
}

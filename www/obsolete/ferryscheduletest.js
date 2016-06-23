// JavaScript source code
// ferry schedule test using objects and date ranges (like the store hours):
//  [{Dates: [mmddfrom, mmddto, ...], St: [hhmm, hhmm, hhmm, ...], SDoW: [0123456, *, ...], 
//   At: [hhmm, hhmm, hhmm, ...], ADoW: [0123456, *, ...], Kt: [hhmm, hhmm, hhmm, ...], KDoW: [0123456, *, ...] } ], ...
FS = [
    {Dates: [1231,1231,0506,0506, 0703,0703,0909,0909,1224,1224], // holiday with a 445 and a 10:30
    St: [445, "12345", 545, "123456", 645, "*", 800, "*", 900, "*", 1000, "*", 1200, "*", 1420, "*", 1520, "*", 1620, "*", 1730, "*", 1840, "*", 2040, "*", 2200, "*", 2300, "*", ],
        At: [515, 615, 730, 830, 930, 1030, 1230, 1450, 1550, 1650, 1800, 1910, 2110, 2230, 2330 ],
        ADow: ["12345", "123456", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*"],
        Kt: [0, 0, 655, 0, 0, 1010, 1255, 0, 0, 0, 0, 1935, 0, 2250, 2350],
        KDow: [, , "*", ,, "*", "*",,,,, "*", "*", "5"]},
    {Dates: [0101,0101,0704,0704,1124,1124,1225,1225],  // holiday with no 445 but a 1030
        St: [0, 545, 645, 800, 900, 1000, 1200, 1420, 1520, 1620, 1730, 1840, 2040, 2200, 2300],
        SDow: [, "123456", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*"],
        At: [0, 615, 730, 830, 930, 1030, 1230, 1450, 1550, 1650, 1800, 1910, 2110, 2230, 2330 ],
        ADow: [, "123456", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*"],
        Kt: [0, 0, 655, 0, 0, 1010, 1255, 0, 0, 0, 0, 1935, 0, 2250, 2350],
        KDow: [, , "*", ,, "*", "*",,,,, "*", "*", "5"]},
    {Dates: [0701, 0831], // july-aug: 11:00 fri, 2 boat runs F & Sun
        St: [445, 545, 645, 800, 900, 1000, 1200, 1420, 1520, 1620, 1730, 1840, 2040, 2200, 2300],
        SDow: ["12345", "123456", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*", "6", "5"],
        At: [515, 615, 730, 830, 930, 1030, 1230, 1450, 1550, 1650, 1800, 1910, 2110, 2230, 2330 ],
        ADow: ["12345", "123456", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*", "6", "5"],
        Kt: [0, 0, 655, 0, 0, 1010, 1255, 0, 0, 0, 0, 1935, 0, 2250, 2350],
        KDow: [, , "*", ,, "*", "*",,,,, "*", "6", "5"]},
    {Dates: [0901, 0905],  // Up till labor day; 11:00 Fri, single boat run
        St: [445, 545, 645, 800, 900, 1000, 1200, 1420, 1520, 1620, 1730, 1840, 2040, 2300],
        SDow: ["12345", "123456", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*", "5"],
        At: [515, 615, 730, 830, 930, 1030, 1230, 1450, 1550, 1650, 1800, 1910, 2110, 2330 ],
        ADow: ["12345", "123456", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*", "5"],
        Kt: [0, 0, 655, 0, 0, 1010, 1255, 0, 0, 0, 0, 1935, 0, 2350],
        KDow: [, , "*", , , "*", "*", , , , , "*", , "5"]}, 
     {Dates: [0906, 1231, 0101, 0630],  // the rest of the year; 110:00 Fri
        St: [445, 545, 645, 800, 900, 1000, 1200, 1420, 1520, 1620, 1730, 1840, 2040, 2200],
        SDow: ["12345", "123456", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*", "5"],
        At: [515, 615, 730, 830, 930, 1030, 1230, 1450, 1550, 1650, 1800, 1910, 2110, 2230 ],
        ADow: ["12345", "123456", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*", "*", "5"],
        Kt: [0, 0, 655, 0, 0, 1010, 1255, 0, 0, 0, 0, 1935, 0, 2250],
        KDow: [, , "*", , , "*", "*", , , , , "*", , "5"]
    }
]
// Algorithm: Find the date range for the date in question\, then use that set of schedules
############################################################################################
#   ferrylog - read the ferry position log and extract important information
#   extracts avg time between ports and at ports (except Steilacoom)
#   
#  reads ferrypositionlog.csv
# #
#  12/17/22. rfb. My very first Python program.
FerryLog='C:/Users/suppo/source/repos/AIA/AndersonIsland/playground/ferrypositionlog.csv'  #don't use \
#FerryLog='C:/Users/suppo/source/repos/AIA/AndersonIsland/playground/ferrylog.txt'  #don't use \LineCount=0
ArSt = 0
DoSt = 0
DkSt = 0
ArAI = 0
DoAI = 0
DkAI = 0
NoMatch = 0
State = ""
Num3MinSteps = 0
Num3MinSteps = 0
TripCount = 0 # total number of trips
# wait time in 3 minute steps with the count in each in element.
# WaitTime[1] = number of runs with a wait time of 1*3 min.  WaitTime[2]=wait time of 2*3  min, 
AIWaitTime=[0,0,0,0,0,0,0,0,0,0,0] 
AITransitTime =[0,0,0,0,0,0,0,0,0,0,0] 
STTransitTime =[0,0,0,0,0,0,0,0,0,0,0] 
STWaitTime =[0,0,0,0,0,0,0,0,0,0,0] 
KeWaitTime =[0,0,0,0,0,0,0,0,0,0,0] 
KeTransitTime = [0,0,0,0,0,0,0,0,0,0,0] 
STKeTransitTime =[0,0,0,0,0,0,0,0,0,0,0]  # St by way of Ketron 
KetronStop = False
LineCount = 0
KeWait = 0

###############################################################################################
# Analyze - analyze one line from the ferry position log.
#   entry   line = text line
#   exit    global counts
#
def Analyze(line):
    global LineCount
    global ArSt
    global DoSt
    global DkSt
    global ArAI
    global DoAI
    global DkAI
    global NoMatch
    global State
    global Num3MinSteps
    global AIWaitTime 
    global AITransitTime
    global STTransitTime 
    global STWaitTime  
    global KeWaitTime  
    global KeTransitTime 
    global STKeTransitTime
    global KetronStop
    global TripCount
    global KeWait

    im = 27 #message
    LineCount = LineCount + 1
    a = line.split(",")
    if(len(a)<im) : return
    m = a[im]  # message

    # Leaving Ketron
    if(m.find("leaving Ketron")> 0):
        ArSt = ArSt + 1
        if(State=="AtKe" and KetronStop):  #if it was at Ke, compute the Ke wait time
            if(Num3MinSteps > 10): Num3MinSteps = 10
            KeWaitTime[Num3MinSteps] += 1
            KeWait += 1
            print (str(KeWait) + "=" + m)
            Num3MinSteps = 0
        Num3MinSteps += 1
        State = "ToSt"

    # Travelling to St
    elif(m.find("' arriving @St")> 0):
        ArSt = ArSt + 1
        if(State=="AtAI"):  #if it was at AI, compute the AI wait time
            if(Num3MinSteps > 10): Num3MinSteps = 10
            AIWaitTime[Num3MinSteps] += 1
            Num3MinSteps = 0
        Num3MinSteps += 1
        State = "ToSt"

    # Docking at St
    elif(m.find("docking @St")> 0):
        DoSt += 1
        Num3MinSteps += 1
        State = "ToSt"

    # Docked at St
    elif(m.find("docked @St")> 0):
        DkSt += 1
        if(State=="ToSt"):  # if it just docked, compute St transittime
            TripCount += 1
            if(Num3MinSteps > 10): Num3MinSteps = 10
            if(KetronStop): 
                STKeTransitTime[Num3MinSteps] += 1
            else:
                 STTransitTime[Num3MinSteps] += 1
            Num3MinSteps = 0
        Num3MinSteps += 1
        State = "AtSt"
        KetronStop = False

    # Travelling to AI
    elif(m.find("arriving @AI")> 0): 
        ArAI = ArAI + 1
        if(State=="AtSt"):  ## If it was at St, compute St wait time
            if(Num3MinSteps > 10): Num3MinSteps = 10
            STWaitTime[Num3MinSteps] += 1
            Num3MinSteps = 0
        Num3MinSteps += 1
        State = "ToAI"

    # Docking at AI
    elif(m.find("docking @AI")> 0):
        DoAI += 1
        Num3MinSteps += 1
        State = "ToAI"

    # Docked at AI
    elif(m.find("docked @AI")> 0):
        DkAI += 1
        if(State=="ToAI"):  # if it just docked
            if(Num3MinSteps > 10): Num3MinSteps = 10
            AITransitTime[Num3MinSteps] += 1  # total transit time
            Num3MinSteps = 0
        State = "AtAI"
        Num3MinSteps += 1
        KetronStop = False

        # Docked at Ketron
    elif(m.find("' at Ketron")> 0):
        DkAI += 1
        if(State=="ToSt"):  # if it just docked
            if(Num3MinSteps > 10): Num3MinSteps = 10
            KeTransitTime[Num3MinSteps] += 1  # total transit time
            Num3MinSteps = 0
        State = "AtKe"
        KetronStop = True
        Num3MinSteps += 1

    else:
        NoMatch += 1


    return

##################################################################################################
# PrintResults - print results of the analysis
#   entry
#   exit    none
#
#helper to print all values in the array
def PrintArray(a):  
    s = ""
    t = 0  # total minutes
    nt = 0  # total runs
    for i in range(11):
        s += (str(i*3) + "=" + str(a[i]) + ", ")
        t += a[i] * i * 3;  # total
        nt += a[i] # counter
    return s +  " runs=" + str(nt) + ", avg=" + str(t/nt)

def PrintResults() :
    print ("linecount=" + str(LineCount))
    print ("Trip Count = " + str(TripCount))
    print ("ArSt=" + str(ArSt) + ", DoSt=" + str(DoSt) + ",DkSt=" + str(DkSt))
    print ("ArAI=" + str(ArAI) + ", DoAI=" + str(DoAI) + ",DkAI=" + str(DkAI))
    print ("Nomatch=" + str(NoMatch))
    
    print ("ST WAIT TIME: " +  PrintArray(STWaitTime))
    print ("ST TRAN TIME: " +  PrintArray(STTransitTime))
    print ("AI WAIT TIME: " +  PrintArray(AIWaitTime))
    print ("AI TRAN TIME: " +  PrintArray(AITransitTime))
    print ("KE TRAN TIME: " +  PrintArray(KeTransitTime))
    print ("KE WAIT TIME: " + PrintArray(KeWaitTime))
    print ("KE->ST TRANSIT TIME: " + PrintArray(STKeTransitTime))
    return


#######################################################################################################

print("hello Bob, I'm mr. Python.")
#  read ferrylog
fp = open(FerryLog, "r")
while(True) :
    line = fp.readline() 
    if(line==""): break
    Analyze(line)
fp.close()

PrintResults()
exit()



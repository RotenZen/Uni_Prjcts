#include <stdio.h>
#include <stdlib.h>
#include <string.h>

struct Patient {
    char name[100];
    char gender;
    char bldgrp[3];
    int contact;
    char guardian[100];
    int room;
    char ill[100];
    int age;
};

void addPatient(struct Patient p[], int *srl);
void savetofile(struct Patient p[], int srl);
void srchPatient(int cntct);
void addRemarks(int cntct);
void rmvPatient(int cntct);
void book(int cntct, char date[]);

int main()
{
    int chc1, chc2 = 0, srl;
    int cntr=0;
    char date[10];
    FILE *srlF;
    srlF = fopen("Serial.txt", "r");
    if (srlF == NULL)
    {
        srl = 0;
    } else
    {
        fscanf(srlF, "%d", &srl);
        fclose(srlF);
    }

    int cntct, sCntct;
    struct Patient p[150];

    while(1)
    {
        LOGin:
        printf("\n1. Patient Login.");
        printf("\n2. Authority Login.\n");
        printf("Enter choice: ");
        scanf("%d", &chc1);

        if (chc1 == 1)
        {
            printf("Enter Contact Info To LogIn: ");
            scanf("%d", &sCntct);
            srchPatient(sCntct);
        }
        else if (chc1 == 2)
        {
            while (chc2 != 8)
            {
                printf("1. ADD PATIENT.\n");
                printf("2. SAVE PATIENT INFO TO FILE\n");
                printf("3. SEARCH PATIENT.\n");
                printf("4. Book ICU/OT.\n");
                printf("5. ADD REMARKS (special needs).\n");
                printf("6. REMOVE PATIENT.\n");
                printf("7. Return to Log In.\n");
                printf("8. EXIT\n");
                printf("Enter choice: ");
                scanf("%d", &chc2);
                getchar();

                switch (chc2)
                {
                    case 1:
                        cntr++;
                        addPatient(p, &srl);
                        srlF = fopen("Serial.txt", "w");
                        fprintf(srlF, "%d", srl);
                        fclose(srlF);
                        break;
                    case 2:
                        printf("%d\n" , cntr);
                        savetofile(p, cntr);
                        printf("Patient Info saved to File\n");
                        break;
                    case 3:
                        printf("Enter Patient contact Info: ");
                        scanf("%d", &sCntct);
                        srchPatient(sCntct);
                        break;
                    case 4:
                        printf("Enter patient Contact info: ");
                        scanf("%d" , &cntct);
                        getchar();
                        printf("Date(day-month-year): ");
                        gets(date);
                        book(cntct , date);
                        break;
                    case 5:
                        printf("Patient contact info: ");
                        scanf("%d", &sCntct);
                        addRemarks(sCntct);
                        break;
                    case 6:
                        printf("Patient contact info: ");
                        scanf("%d", &sCntct);
                        rmvPatient(sCntct);
                        srl--;
                        srlF = fopen("Serial.txt", "w");
                        fprintf(srlF, "%d", srl);
                        fclose(srlF);
                        break;
                    case 7:
                        goto LOGin;
                        break;
                    case 8:
                        return 0;
                }
            }
        }
        else
            printf("WRONG INPUT. Try again.\n");
    }

    return 0;
}

void addPatient(struct Patient p[], int *srl)
{
    printf("Name: ");
    gets(p[*srl].name);
    printf("Gender (F/M): ");
    scanf("%c", &p[*srl].gender);
    getchar();
    printf("Blood Group: ");
    gets(p[*srl].bldgrp);
    printf("Contact: ");
    scanf("%d", &p[*srl].contact);
    getchar();
    printf("Disease: ");
    gets(p[*srl].ill);
    printf("Guardian: ");
    gets(p[*srl].guardian);
    printf("Room: 0%d\n", p[*srl].room = *srl + 1);
    printf("Age: ");
    scanf("%d", &p[*srl].age);
    getchar();

    (*srl)++;
}

void savetofile(struct Patient p[], int cntr)
{
    for (int i = 0; i < cntr; i++) {
        char filename[20];
        sprintf(filename, "%d.txt", p[i].contact);
        FILE *pFile = fopen(filename, "a");

        if (pFile == NULL)
        {
            perror("Error opening file.");
            continue;
        }

        fprintf(pFile, "Patient %d\n", i + 1);
        fprintf(pFile, "Name: %s\n", p[i].name);
        fprintf(pFile, "Gender: %c\n", p[i].gender);
        fprintf(pFile, "Blood Group: %s\n", p[i].bldgrp);
        fprintf(pFile, "Contact: %d\n", p[i].contact);
        fprintf(pFile, "Disease: %s\n", p[i].ill);
        fprintf(pFile, "Guardian: %s\n", p[i].guardian);
        fprintf(pFile, "Room: %d\n", p[i].room);
        fprintf(pFile, "Age: %d\n", p[i].age);
        fprintf(pFile, "\n");

        fclose(pFile);
    }
}

void srchPatient(int cntct)
{
    FILE *pFile;
    char fileName[20];
    char line[1000];
    sprintf(fileName, "%d.txt", cntct);
    pFile = fopen(fileName, "r");

    if (pFile == NULL)
    {
        perror("Error opening file\n");
        return;
    }
    while (fgets(line, sizeof(line), pFile) != NULL)
    {
        printf("%s", line);
    }
    fclose(pFile);
}

void addRemarks(int cntct)
{
    FILE *pFile;
    char fileName[20];
    char line[1000];
    sprintf(fileName, "%d.txt", cntct);
    pFile = fopen(fileName, "a");

    if (pFile == NULL)
    {
        perror("Error opening file\n");
        return;
    }
    getchar();
    printf("\nEnter remarks: ");
    gets(line);

    fprintf(pFile, "%s\n", line);

    fclose(pFile);
}

void rmvPatient(int cntct)
{
    char filename[20];
    sprintf(filename, "%d.txt", cntct);
    if (remove(filename) == 0)
    {
        printf("Patient removed successfully.\n");
    }
    else
    {
        printf("Error: Removing Patient Failed.\n");
    }
}

void book(int cntct, char date[])
{
    FILE *icuF;
    icuF=fopen("IcuRES.txt" , "a");
    if(icuF==NULL)
    {
        perror("Error opening file");
        return 0;
    }
    fprintf(icuF , "%d\t\t\t\t" , cntct);
    fprintf(icuF , "%s" , date);

    fclose(icuF);
}


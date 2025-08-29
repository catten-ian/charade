# Makefile to replace build_and_run.bat
# Note: This Makefile requires GNU Make, which is not included with Windows by default.
# To use this Makefile, you can:
# 1. Install MinGW or Cygwin which include GNU Make
# 2. Use WSL (Windows Subsystem for Linux)
# 3. Alternatively, continue using build_and_run.bat for Windows

# Define paths
MARIADB_INCLUDE := E:\software\wampsever\bin\mariadb\mariadb11.3.2\include\mysql
MARIADB_LIB := E:\software\wampsever\bin\mariadb\mariadb11.3.2\lib

# Define compiler and flags
CXX := g++
CXXFLAGS := -I$(MARIADB_INCLUDE)
LDFLAGS := -L$(MARIADB_LIB)
LDLIBS := -lmariadb

# Define target executable
TARGET := user_status_updater.exe

# Source files
SOURCES := UserStatusUpdater.cpp

# Default target
all: $(TARGET)

# Compile target
$(TARGET): $(SOURCES)
	@echo Setting environment variables...
	@set PATH=$(MARIADB_LIB);%PATH%
	@echo Compiling UserStatusUpdater.cpp with MariaDB libraries...
	$(CXX) $(CXXFLAGS) $(LDFLAGS) $(SOURCES) -o $(TARGET) $(LDLIBS)
	@if errorlevel 1 (
		echo Compilation failed!
		exit 1
	) else (
		echo Compilation successful!
	)

# Run target
run: $(TARGET)
	@echo Running $(TARGET)...
	@$(TARGET)
	@echo Program exit code: %ERRORLEVEL%
	@pause

# Clean target
clean:
	@if exist $(TARGET) del $(TARGET)
	@echo Clean completed.

# Phony targets
.PHONY: all run clean